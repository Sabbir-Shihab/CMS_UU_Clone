#!/usr/bin/env python3
"""Download attached WP media for every local news/event post and fill missing covers."""

from __future__ import annotations

import sqlite3
import sys
from concurrent.futures import ThreadPoolExecutor, as_completed
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from import_news_db import MEDIA, attached_urls, download, ext_for, localize_content  # noqa: E402

ROOT = Path("/home/jim/Documents/UU Website")
DB = ROOT / "database" / "uu.sqlite"


def fill_one(row: tuple) -> tuple[str, str, str]:
    slug, wp_id, image_path, content = row
    extras = attached_urls(wp_id)
    content = localize_content(content or "", slug, extras)
    cover = image_path or ""
    if not cover or not str(cover).startswith("/uploads/"):
        if extras:
            dest = MEDIA / slug / f"cover{ext_for(extras[0])}"
            if download(extras[0], dest):
                cover = f"/uploads/news/{slug}/{dest.name}"
        if not cover or not str(cover).startswith("/uploads/"):
            folder = MEDIA / slug
            if folder.exists():
                files = sorted(p for p in folder.iterdir() if p.is_file())
                if files:
                    cover = f"/uploads/news/{slug}/{files[0].name}"
    return slug, cover, content


def main():
    con = sqlite3.connect(DB)
    rows = con.execute("SELECT slug, wp_id, image_path, content FROM news_posts").fetchall()
    print(f"filling media for {len(rows)} posts…", flush=True)
    updated = 0
    covers = 0
    with ThreadPoolExecutor(max_workers=8) as pool:
        futs = [pool.submit(fill_one, row) for row in rows]
        done = 0
        for fut in as_completed(futs):
            slug, cover, content = fut.result()
            con.execute(
                "UPDATE news_posts SET image_path = ?, content = ? WHERE slug = ?",
                (cover, content, slug),
            )
            updated += 1
            if str(cover).startswith("/uploads/"):
                covers += 1
            done += 1
            if done % 20 == 0 or done == len(rows):
                print(f"  {done}/{len(rows)}", flush=True)
    con.commit()
    empty = con.execute(
        "SELECT COUNT(*) FROM news_posts WHERE image_path IS NULL OR image_path = ''"
    ).fetchone()[0]
    print(f"updated {updated}, local covers {covers}, still empty {empty}", flush=True)
    con.close()


if __name__ == "__main__":
    main()
