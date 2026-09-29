#!/usr/bin/env python3
import html
import json
import re
import urllib.request
import uuid
from datetime import datetime
from pathlib import Path

ROOT = Path("/home/jim/Documents/UU Website")
PAGES = ROOT / "storage/app/cms/pages.json"


def fetch_posts():
    url = "https://www.uttara.ac.bd/wp-json/wp/v2/posts?per_page=50&_embed=1"
    with urllib.request.urlopen(url, timeout=60) as res:
        return json.loads(res.read().decode("utf-8"))


def clean_html(raw: str) -> str:
    raw = re.sub(r"<script[\s\S]*?</script>", "", raw, flags=re.I)
    raw = re.sub(r'\ssrcset="[^"]*"', "", raw)
    raw = re.sub(r'\ssizes="[^"]*"', "", raw)
    raw = re.sub(r'\sdata-[a-z0-9-]+="[^"]*"', "", raw)
    raw = re.sub(r'\sdecoding="[^"]*"', "", raw)
    raw = re.sub(r'\sloading="[^"]*"', "", raw)
    raw = re.sub(r'\swidth="[^"]*"', "", raw)
    raw = re.sub(r'\sheight="[^"]*"', "", raw)
    return raw.strip()


def featured_image(post: dict, content: str) -> str:
    embedded = post.get("_embedded") or {}
    media = embedded.get("wp:featuredmedia") or []
    if media:
        src = media[0].get("source_url") or ""
        if src:
            return src
    m = re.search(r'<img[^>]+src="([^"]+)"', content)
    return m.group(1) if m else ""


def format_date(iso: str) -> str:
    dt = datetime.fromisoformat(iso.replace("Z", "+00:00"))
    return dt.strftime("%-d %B, %Y")


def article_body(post: dict) -> str:
    title = html.unescape(re.sub(r"<[^>]+>", "", post["title"]["rendered"]))
    content = clean_html(post["content"]["rendered"])
    date = format_date(post["date"])
    img = featured_image(post, content)
    img_html = ""
    if img:
        img_html = f'<img src="{html.escape(img)}" alt="{html.escape(title)}" class="img-fluid img-thumbnail">'
    slug = post["slug"]
    share = f"""<div class="news-share"><h3>Share</h3>
      <a href="https://www.facebook.com/sharer/sharer.php?u=https://www.uttara.ac.bd/{slug}/" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
      <a href="https://twitter.com/intent/tweet?url=https://www.uttara.ac.bd/{slug}/" target="_blank" rel="noopener" aria-label="Twitter"><i class="fa-brands fa-x-twitter"></i></a>
      <a href="https://www.linkedin.com/shareArticle?mini=true&url=https://www.uttara.ac.bd/{slug}/" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
    </div>"""
    return f"""<div class="about-us-banner single-page-banner"><div class="container">
  <h3 class="dept-name-common">{html.escape(title)}</h3>
  <p class="article-date"><i class="fa-regular fa-calendar-days"></i> {date}</p>
</div></div>
<main class="page-wrap news-article">
  <div class="container">
    <div class="description text-center">
      {img_html}
      <div class="text-dark text-left chancellor-para mb-3">{content}</div>
      {share}
    </div>
  </div>
</main>
"""


def norm(text: str) -> str:
    text = html.unescape(text)
    text = re.sub(r"<[^>]+>", "", text)
    text = text.replace("…", "").replace("...", "")
    text = re.sub(r"[^a-z0-9]+", "", text.lower())
    return text


def rewrite_links(body: str, posts: list[dict]) -> str:
    catalog = [(norm(html.unescape(re.sub(r"<[^>]+>", "", p["title"]["rendered"]))), p["slug"]) for p in posts]

    def repl(match: re.Match) -> str:
        prefix, _href, quote, rest, inner, close = match.groups()
        label = re.sub(r"\s+", " ", re.sub(r"<[^>]+>", "", inner)).strip()
        if label.lower() in {"news & events", "see more", "notice & announcements", "view all"}:
            return match.group(0)
        key = norm(label)
        if len(key) < 12:
            return match.group(0)
        for title_key, slug in catalog:
            if key == title_key or title_key.startswith(key) or key.startswith(title_key[:40]):
                return f"{prefix}/{slug}{quote}{rest}{inner}{close}"
        return match.group(0)

    return re.sub(
        r'(<a\b[^>]*href=")([^"]*)(")([^>]*>)(.*?)(</a>)',
        repl,
        body,
        flags=re.S | re.I,
    )


def main():
    posts = fetch_posts()
    pages = json.loads(PAGES.read_text())
    by_slug = {p["slug"]: i for i, p in enumerate(pages)}
    added = 0
    for post in posts:
        slug = post["slug"]
        title = html.unescape(re.sub(r"<[^>]+>", "", post["title"]["rendered"]))
        page = {
            "slug": slug,
            "title": f"{title} | Uttara University",
            "menu_key": "news",
            "body": article_body(post),
        }
        if slug in by_slug:
            existing = pages[by_slug[slug]]
            page["id"] = existing["id"]
            pages[by_slug[slug]] = {**existing, **page}
        else:
            page["id"] = str(uuid.uuid4())
            pages.append(page)
            by_slug[slug] = len(pages) - 1
            added += 1

    for slug in ("home", "news", "notices"):
        i = by_slug.get(slug)
        if i is None:
            continue
        pages[i]["body"] = rewrite_links(pages[i]["body"], posts)

    pages.sort(key=lambda p: p.get("slug") or "")
    PAGES.write_text(json.dumps(pages, ensure_ascii=False, indent=2) + "\n")
    print(f"posts={len(posts)} added={added} total_pages={len(pages)}")


if __name__ == "__main__":
    main()
