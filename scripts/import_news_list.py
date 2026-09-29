#!/usr/bin/env python3
import html
import json
import urllib.request
from datetime import datetime
from pathlib import Path

OUT = Path("/home/jim/Documents/UU Website/storage/app/cms/news-list.json")


def fetch(page: int) -> tuple[list, int]:
    url = f"https://www.uttara.ac.bd/wp-json/wp/v2/posts?categories_exclude=21&per_page=100&page={page}&_embed=1"
    req = urllib.request.Request(url)
    with urllib.request.urlopen(req, timeout=60) as res:
        total_pages = int(res.headers.get("X-WP-TotalPages", "1"))
        return json.loads(res.read().decode("utf-8")), total_pages


def image_for(post: dict) -> str:
    media = (post.get("_embedded") or {}).get("wp:featuredmedia") or []
    if media:
        src = media[0].get("source_url") or ""
        if src:
            return src
    content = post.get("content", {}).get("rendered") or ""
    import re
    m = re.search(r'<img[^>]+src="([^"]+)"', content)
    return m.group(1) if m else ""


def main():
    items = []
    page = 1
    total_pages = 1
    while page <= total_pages:
        posts, total_pages = fetch(page)
        for post in posts:
            title = html.unescape(post["title"]["rendered"])
            dt = datetime.fromisoformat(post["date"].replace("Z", "+00:00"))
            items.append({
                "slug": post["slug"],
                "title": title,
                "date": dt.strftime("%-d %B, %Y"),
                "image": image_for(post),
            })
        page += 1
    OUT.write_text(json.dumps(items, ensure_ascii=False, indent=2) + "\n")
    print(f"wrote {len(items)} items")


if __name__ == "__main__":
    main()
