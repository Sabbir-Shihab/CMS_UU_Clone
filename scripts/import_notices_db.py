#!/usr/bin/env python3
"""Download UU notices (category 21) plus images/PDFs into database/uu.sqlite."""

from __future__ import annotations

import html
import json
import re
import sqlite3
import time
import urllib.error
import urllib.parse
import urllib.request
from concurrent.futures import ThreadPoolExecutor, as_completed
from datetime import datetime
from pathlib import Path

ROOT = Path("/home/jim/Documents/UU Website")
DB = ROOT / "database" / "uu.sqlite"
SCHEMA = ROOT / "database" / "schema.sql"
MEDIA = ROOT / "public" / "uploads" / "notices"
UA = "Mozilla/5.0 UU-local-import"

FILE_RE = re.compile(
    r'https://www\.uttara\.ac\.bd/+wp-content/uploads/[^"\'\s>]+\.(?:webp|jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx)',
    re.I,
)


def fetch_json(url: str):
    req = urllib.request.Request(url, headers={"User-Agent": UA})
    with urllib.request.urlopen(req, timeout=60) as res:
        headers = dict(res.headers.items())
        return json.loads(res.read().decode("utf-8")), headers


def encode_url(url: str) -> str:
    parts = urllib.parse.urlsplit(url)
    path = urllib.parse.quote(re.sub(r"/+", "/", parts.path), safe="/")
    return urllib.parse.urlunsplit((parts.scheme, parts.netloc, path, parts.query, parts.fragment))


def download(url: str, dest: Path) -> bool:
    dest.parent.mkdir(parents=True, exist_ok=True)
    if dest.exists() and dest.stat().st_size > 200:
        return True
    req = urllib.request.Request(encode_url(url), headers={"User-Agent": UA})
    try:
        with urllib.request.urlopen(req, timeout=60) as res:
            data = res.read()
        if not data or len(data) < 50:
            return False
        dest.write_bytes(data)
        return True
    except (urllib.error.URLError, TimeoutError, OSError, UnicodeEncodeError):
        return False


def ext_for(url: str, mime: str = "") -> str:
    path = url.split("?", 1)[0].lower()
    for ext in (".webp", ".jpg", ".jpeg", ".png", ".gif", ".pdf", ".doc", ".docx", ".xls", ".xlsx"):
        if path.endswith(ext):
            return ext
    mime = (mime or "").lower()
    if "pdf" in mime:
        return ".pdf"
    if "png" in mime:
        return ".png"
    if "webp" in mime:
        return ".webp"
    if "jpeg" in mime or "jpg" in mime:
        return ".jpg"
    if "sheet" in mime or "excel" in mime:
        return ".xlsx"
    if "word" in mime or "document" in mime:
        return ".docx"
    return ".bin"


def attached_media(wp_id: int) -> list[dict]:
    if not wp_id:
        return []
    try:
        data, _ = fetch_json(
            f"https://www.uttara.ac.bd/wp-json/wp/v2/media?parent={int(wp_id)}&per_page=100"
        )
    except (urllib.error.URLError, TimeoutError, json.JSONDecodeError):
        return []
    items = []
    for item in data or []:
        src = item.get("source_url") or ""
        if not src:
            continue
        items.append({
            "url": src,
            "mime": item.get("mime_type") or "",
            "title": html.unescape(re.sub(r"<[^>]+>", "", (item.get("title") or {}).get("rendered") or "Attachment")),
        })
    return items


def media_dir_name(slug: str) -> str:
    """Filesystem/URL path segment: decode WP percent-encoded slugs so static files resolve."""
    name = urllib.parse.unquote(slug or "").replace("/", "-").replace("\x00", "")
    return name or "notice"


def localize_content(content: str, slug: str, extras: list[dict]) -> tuple[str, str]:
    urls = []
    for url in FILE_RE.findall(content or ""):
        if url not in urls:
            urls.append(url)
    for item in extras:
        if item["url"] not in urls:
            urls.append(item["url"])

    folder = media_dir_name(slug)
    mapping = {}
    image_path = ""
    pdf_links = []
    for i, url in enumerate(urls, start=1):
        mime = next((x["mime"] for x in extras if x["url"] == url), "")
        ext = ext_for(url, mime)
        kind = "pdf" if ext == ".pdf" else ("doc" if ext in (".doc", ".docx") else "img")
        dest = MEDIA / folder / f"{kind}-{i}{ext}"
        if download(url, dest):
            local = f"/uploads/notices/{folder}/{dest.name}"
            mapping[url] = local
            if kind == "img" and not image_path:
                image_path = local
            if kind in ("pdf", "doc"):
                title = next((x["title"] for x in extras if x["url"] == url), dest.name)
                pdf_links.append((local, title or dest.name))

    for remote, local in mapping.items():
        content = content.replace(remote, local)
    content = re.sub(r'\ssrcset="[^"]*"', "", content)
    content = re.sub(r'\ssizes="[^"]*"', "", content)

    # empty-content notices that are image/PDF only
    if not (content or "").strip():
        parts = []
        if image_path:
            parts.append(f'<img src="{image_path}" alt="" class="img-fluid img-thumbnail">')
        for local, title in pdf_links:
            parts.append(f'<p><a href="{local}" target="_blank" rel="noopener">{html.escape(title)}</a></p>')
        content = "\n".join(parts)
    else:
        # append any PDF links not already in content
        for local, title in pdf_links:
            if local not in content:
                content += f'<p><a href="{local}" target="_blank" rel="noopener">{html.escape(title)}</a></p>'

    return content, image_path


def load_posts() -> list[dict]:
    posts = []
    page = 1
    total_pages = 1
    while page <= total_pages:
        url = (
            "https://www.uttara.ac.bd/wp-json/wp/v2/posts"
            f"?categories=21&per_page=100&page={page}&_embed=1"
        )
        data, headers = fetch_json(url)
        total_pages = int(headers.get("X-WP-TotalPages") or headers.get("x-wp-totalpages") or 1)
        posts.extend(data)
        print(f"fetched page {page}/{total_pages} ({len(posts)} notices)", flush=True)
        page += 1
    return posts


def featured_from_embed(post: dict) -> str:
    media = (post.get("_embedded") or {}).get("wp:featuredmedia") or []
    if media:
        return media[0].get("source_url") or ""
    return ""


def import_one(post: dict) -> dict:
    slug = post["slug"]
    title = html.unescape(re.sub(r"<[^>]+>", "", post["title"]["rendered"]))
    content = post.get("content", {}).get("rendered") or ""
    published = post.get("date") or ""
    try:
        dt = datetime.fromisoformat(published.replace("Z", "+00:00"))
        display = f"{dt.day} {dt.strftime('%B, %Y')}"
        day_label = str(dt.day)
        month_label = dt.strftime("%b")
        published_at = dt.strftime("%Y-%m-%d %H:%M:%S")
    except ValueError:
        display = published
        day_label = ""
        month_label = ""
        published_at = published

    extras = attached_media(post.get("id") or 0)
    featured = featured_from_embed(post)
    if featured and not any(x["url"] == featured for x in extras):
        extras.insert(0, {"url": featured, "mime": "", "title": title})

    content, image_path = localize_content(content, slug, extras)
    if not image_path and featured:
        folder = media_dir_name(slug)
        dest = MEDIA / folder / f"cover{ext_for(featured)}"
        if download(featured, dest):
            image_path = f"/uploads/notices/{folder}/{dest.name}"

    now = time.strftime("%Y-%m-%d %H:%M:%S")
    return {
        "wp_id": post.get("id"),
        "slug": slug,
        "title": title,
        "display_date": display,
        "day_label": day_label,
        "month_label": month_label,
        "published_at": published_at,
        "image_path": image_path,
        "content": content,
        "created_at": now,
        "updated_at": now,
    }


def main():
    MEDIA.mkdir(parents=True, exist_ok=True)
    DB.parent.mkdir(parents=True, exist_ok=True)
    con = sqlite3.connect(DB)
    con.executescript(SCHEMA.read_text())
    # older DBs may miss image_path
    cols = [r[1] for r in con.execute("PRAGMA table_info(notice_posts)").fetchall()]
    if cols and "image_path" not in cols:
        con.execute("ALTER TABLE notice_posts ADD COLUMN image_path TEXT")
    con.commit()

    posts = load_posts()
    print(f"importing {len(posts)} notices with media…", flush=True)
    rows = []
    with ThreadPoolExecutor(max_workers=8) as pool:
        futs = [pool.submit(import_one, post) for post in posts]
        done = 0
        for fut in as_completed(futs):
            rows.append(fut.result())
            done += 1
            if done % 25 == 0 or done == len(posts):
                print(f"  processed {done}/{len(posts)}", flush=True)

    con.execute("DELETE FROM notice_posts")
    con.executemany(
        """
        INSERT INTO notice_posts
        (wp_id, slug, title, display_date, day_label, month_label, published_at, image_path, content, created_at, updated_at)
        VALUES
        (:wp_id, :slug, :title, :display_date, :day_label, :month_label, :published_at, :image_path, :content, :created_at, :updated_at)
        """,
        rows,
    )
    con.commit()
    with_img = sum(1 for r in rows if str(r.get("image_path") or "").startswith("/uploads/"))
    with_body = sum(1 for r in rows if (r.get("content") or "").strip())
    files = sum(1 for p in MEDIA.rglob("*") if p.is_file())
    print(f"saved {len(rows)} notices, local images {with_img}, with content {with_body}, files {files}", flush=True)
    con.close()


if __name__ == "__main__":
    main()
