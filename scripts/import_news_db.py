#!/usr/bin/env python3
"""Download UU news/events into database/uu.sqlite and save images under public/uploads/news/."""

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
MEDIA = ROOT / "public" / "uploads" / "news"
UA = "Mozilla/5.0 UU-local-import"

IMG_RE = re.compile(
    r'https://www\.uttara\.ac\.bd/+wp-content/uploads/[^"\'\s>]+',
    re.I,
)


def fetch_json(url: str):
    req = urllib.request.Request(url, headers={"User-Agent": UA})
    with urllib.request.urlopen(req, timeout=60) as res:
        headers = dict(res.headers.items())
        return json.loads(res.read().decode("utf-8")), headers


def encode_url(url: str) -> str:
    parts = urllib.parse.urlsplit(url)
    path = urllib.parse.quote(parts.path, safe="/")
    return urllib.parse.urlunsplit((parts.scheme, parts.netloc, path, parts.query, parts.fragment))


def download(url: str, dest: Path) -> bool:
    dest.parent.mkdir(parents=True, exist_ok=True)
    if dest.exists() and dest.stat().st_size > 800:
        return True
    req = urllib.request.Request(encode_url(url), headers={"User-Agent": UA})
    try:
        with urllib.request.urlopen(req, timeout=45) as res:
            data = res.read()
        if not data or len(data) < 200:
            return False
        dest.write_bytes(data)
        return True
    except (urllib.error.URLError, TimeoutError, OSError, UnicodeEncodeError):
        return False


def ext_for(url: str) -> str:
    path = url.split("?", 1)[0].lower()
    for ext in (".webp", ".jpg", ".jpeg", ".png", ".gif"):
        if path.endswith(ext):
            return ext
    return ".jpg"


def featured_url(post: dict) -> str:
    media = (post.get("_embedded") or {}).get("wp:featuredmedia") or []
    if media:
        src = media[0].get("source_url") or ""
        if src:
            return src
    content = post.get("content", {}).get("rendered") or ""
    m = IMG_RE.search(content)
    return m.group(0) if m else ""


def attached_urls(wp_id: int) -> list[str]:
    if not wp_id:
        return []
    try:
        data, _ = fetch_json(
            f"https://www.uttara.ac.bd/wp-json/wp/v2/media?parent={int(wp_id)}&per_page=100"
        )
    except (urllib.error.URLError, TimeoutError, json.JSONDecodeError):
        return []
    urls = []
    for item in data or []:
        src = item.get("source_url") or ""
        if src and src not in urls:
            urls.append(src)
    return urls


def media_dir_name(slug: str) -> str:
    name = urllib.parse.unquote(slug or "").replace("/", "-").replace("\x00", "")
    return name or "news"


def localize_content(content: str, slug: str, extra_urls: list[str] | None = None) -> str:
    urls = []
    for url in IMG_RE.findall(content) + list(extra_urls or []):
        if url not in urls:
            urls.append(url)
    folder = media_dir_name(slug)
    mapping = {}
    for i, url in enumerate(urls, start=1):
        dest = MEDIA / folder / f"img-{i}{ext_for(url)}"
        if download(url, dest):
            mapping[url] = f"/uploads/news/{folder}/{dest.name}"
    for remote, local in mapping.items():
        content = content.replace(remote, local)
    content = re.sub(r'\ssrcset="[^"]*"', "", content)
    content = re.sub(r'\ssizes="[^"]*"', "", content)
    missing_local = [p for p in mapping.values() if p not in content]
    if missing_local:
        gallery = "".join(f'<img src="{p}" alt="" class="img-fluid img-thumbnail">' for p in missing_local)
        content += f'<div class="news-gallery">{gallery}</div>'
    return content


def load_posts() -> list[dict]:
    posts = []
    page = 1
    total_pages = 1
    while page <= total_pages:
        url = (
            "https://www.uttara.ac.bd/wp-json/wp/v2/posts"
            f"?categories_exclude=21&per_page=100&page={page}&_embed=1"
        )
        data, headers = fetch_json(url)
        total_pages = int(headers.get("X-WP-TotalPages") or headers.get("x-wp-totalpages") or 1)
        posts.extend(data)
        print(f"fetched page {page}/{total_pages} ({len(posts)} posts)", flush=True)
        page += 1
    return posts


def import_one(post: dict) -> dict:
    try:
        return _import_one(post)
    except Exception as exc:  # noqa: BLE001
        slug = post.get("slug") or "unknown"
        print(f"  skip {slug}: {exc}", flush=True)
        now = time.strftime("%Y-%m-%d %H:%M:%S")
        title = html.unescape(re.sub(r"<[^>]+>", "", (post.get("title") or {}).get("rendered") or slug))
        return {
            "wp_id": post.get("id"),
            "slug": slug,
            "title": title,
            "display_date": "",
            "published_at": post.get("date") or "",
            "image_path": "",
            "content": (post.get("content") or {}).get("rendered") or "",
            "created_at": now,
            "updated_at": now,
        }


def _import_one(post: dict) -> dict:
    slug = post["slug"]
    title = html.unescape(re.sub(r"<[^>]+>", "", post["title"]["rendered"]))
    content = post.get("content", {}).get("rendered") or ""
    published = post.get("date") or ""
    try:
        dt = datetime.fromisoformat(published.replace("Z", "+00:00"))
        display = f"{dt.day} {dt.strftime('%B, %Y')}"
        published_at = dt.strftime("%Y-%m-%d %H:%M:%S")
    except ValueError:
        display = published
        published_at = published

    extras = attached_urls(post.get("id") or 0)
    cover = featured_url(post) or (extras[0] if extras else "")
    image_path = ""
    folder = media_dir_name(slug)
    if cover:
        dest = MEDIA / folder / f"cover{ext_for(cover)}"
        if download(cover, dest):
            image_path = f"/uploads/news/{folder}/{dest.name}"
        else:
            image_path = cover

    content = localize_content(content, slug, extras)
    now = time.strftime("%Y-%m-%d %H:%M:%S")
    return {
        "wp_id": post.get("id"),
        "slug": slug,
        "title": title,
        "display_date": display,
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
    con.commit()

    posts = load_posts()
    print(f"importing {len(posts)} posts with images…", flush=True)
    rows = []
    with ThreadPoolExecutor(max_workers=8) as pool:
        futs = {pool.submit(import_one, post): post.get("slug") for post in posts}
        done = 0
        for fut in as_completed(futs):
            rows.append(fut.result())
            done += 1
            if done % 20 == 0 or done == len(posts):
                print(f"  processed {done}/{len(posts)}", flush=True)

    con.execute("DELETE FROM news_posts")
    con.executemany(
        """
        INSERT INTO news_posts (wp_id, slug, title, display_date, published_at, image_path, content, created_at, updated_at)
        VALUES (:wp_id, :slug, :title, :display_date, :published_at, :image_path, :content, :created_at, :updated_at)
        """,
        rows,
    )
    con.commit()
    local_covers = sum(1 for r in rows if str(r["image_path"]).startswith("/uploads/"))
    print(f"saved {len(rows)} rows, local covers {local_covers}", flush=True)
    con.close()


if __name__ == "__main__":
    main()
