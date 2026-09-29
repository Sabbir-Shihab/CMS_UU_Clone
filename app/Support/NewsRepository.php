<?php

namespace App\Support;

class NewsRepository
{
    public function __construct(private SqliteStore $db) {}

    public function boot(): void
    {
        $this->db->ensure();
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        $this->boot();

        return $this->db->select(
            'SELECT slug, title, display_date AS date, image_path AS image FROM news_posts ORDER BY published_at DESC, id DESC'
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function latest(int $limit = 9): array
    {
        $this->boot();

        return $this->db->select(
            'SELECT slug, title, display_date AS date, image_path AS image FROM news_posts ORDER BY published_at DESC, id DESC LIMIT ?',
            [$limit]
        );
    }

    /** @return array{items: array<int, array<string, mixed>>, page: int, pages: int, total: int} */
    public function paginate(int $page, int $perPage = 12): array
    {
        $this->boot();
        $countRow = $this->db->select('SELECT COUNT(*) AS total FROM news_posts');
        $total = (int) ($countRow[0]['total'] ?? 0);
        $pages = max(1, (int) ceil(max($total, 1) / $perPage));
        if ($total === 0) {
            $pages = 1;
        }
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;
        $items = $this->db->select(
            'SELECT slug, title, display_date AS date, image_path AS image FROM news_posts ORDER BY published_at DESC, id DESC LIMIT ? OFFSET ?',
            [$perPage, $offset]
        );

        return [
            'items' => $items,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
        ];
    }

    public function find(string $slug): ?array
    {
        $this->boot();
        $rows = $this->db->select('SELECT * FROM news_posts WHERE slug = ? LIMIT 1', [$slug]);

        return $rows[0] ?? null;
    }

    /**
     * @return array<int, array{title: string, slug: string, date: string, type: string}>
     */
    public function search(string $query, int $limit = 25): array
    {
        $this->boot();
        $term = trim($query);
        if ($term === '') {
            return [];
        }

        $like = '%'.$term.'%';
        $rows = $this->db->select(
            'SELECT slug, title, display_date AS date FROM news_posts
             WHERE title LIKE ? OR slug LIKE ? OR content LIKE ?
             ORDER BY published_at DESC, id DESC LIMIT ?',
            [$like, $like, $like, $limit]
        );

        return array_map(static function (array $row) {
            return [
                'title' => (string) ($row['title'] ?? $row['slug'] ?? ''),
                'slug' => (string) ($row['slug'] ?? ''),
                'date' => (string) ($row['date'] ?? ''),
                'type' => 'News',
            ];
        }, $rows);
    }

    public function count(): int
    {
        $this->boot();
        $rows = $this->db->select('SELECT COUNT(*) AS total FROM news_posts');

        return (int) ($rows[0]['total'] ?? 0);
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, page: int, pages: int, total: int, q: string}
     */
    public function adminPaginate(int $page, int $perPage = 20, string $q = ''): array
    {
        $this->boot();
        $q = trim($q);
        $params = [];
        $where = '';
        if ($q !== '') {
            $where = ' WHERE title LIKE ? OR slug LIKE ? OR content LIKE ?';
            $like = '%'.$q.'%';
            $params = [$like, $like, $like];
        }
        $countRow = $this->db->select('SELECT COUNT(*) AS total FROM news_posts'.$where, $params);
        $total = (int) ($countRow[0]['total'] ?? 0);
        $pages = max(1, (int) ceil(max($total, 1) / $perPage));
        if ($total === 0) {
            $pages = 1;
        }
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;
        $items = $this->db->select(
            'SELECT id, slug, title, display_date, image_path, published_at, updated_at
             FROM news_posts'.$where.'
             ORDER BY published_at DESC, id DESC LIMIT ? OFFSET ?',
            [...$params, $perPage, $offset]
        );

        return [
            'items' => $items,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
            'q' => $q,
        ];
    }

    public function findById(int $id): ?array
    {
        $this->boot();
        $rows = $this->db->select('SELECT * FROM news_posts WHERE id = ? LIMIT 1', [$id]);

        return $rows[0] ?? null;
    }

    /**
     * @param  array{slug: string, title: string, display_date: string, published_at: string, image_path: string, content: string}  $data
     */
    public function create(array $data): int
    {
        $this->boot();
        $now = now()->toDateTimeString();
        $result = $this->db->execute(
            'INSERT INTO news_posts (wp_id, slug, title, display_date, published_at, image_path, content, gallery_json, content_after, created_at, updated_at)
             VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['slug'],
                $data['title'],
                $data['display_date'],
                $data['published_at'],
                $data['image_path'] ?? '',
                $data['content'] ?? '',
                $data['gallery_json'] ?? '[]',
                $data['content_after'] ?? '',
                $now,
                $now,
            ]
        );

        return (int) ($result['lastrowid'] ?? 0);
    }

    /**
     * @param  array{slug: string, title: string, display_date: string, published_at: string, image_path: string, content: string}  $data
     */
    public function update(int $id, array $data): void
    {
        $this->boot();
        $this->db->execute(
            'UPDATE news_posts
             SET slug = ?, title = ?, display_date = ?, published_at = ?, image_path = ?, content = ?, gallery_json = ?, content_after = ?, updated_at = ?
             WHERE id = ?',
            [
                $data['slug'],
                $data['title'],
                $data['display_date'],
                $data['published_at'],
                $data['image_path'] ?? '',
                $data['content'] ?? '',
                $data['gallery_json'] ?? '[]',
                $data['content_after'] ?? '',
                now()->toDateTimeString(),
                $id,
            ]
        );
    }

    public function delete(int $id): void
    {
        $this->boot();
        $this->db->execute('DELETE FROM news_posts WHERE id = ?', [$id]);
    }

    public function articlePage(string $slug): ?array
    {
        $row = $this->find($slug);
        if (! $row) {
            return null;
        }

        return [
            'slug' => $slug,
            'title' => ($row['title'] ?? $slug).' | Uttara University',
            'menu_key' => 'news',
            'body' => view('site.partials.news-article', [
                'title' => $row['title'] ?? '',
                'date' => $row['display_date'] ?? '',
                'image' => $row['image_path'] ?? '',
                'content' => $row['content'] ?? '',
                'contentAfter' => $row['content_after'] ?? '',
                'galleryRows' => NewsGallery::rows(NewsGallery::decode($row['gallery_json'] ?? null)),
                'slug' => $slug,
            ])->render(),
        ];
    }
}
