<?php

namespace App\Support;

class SiteContent
{
    public function __construct(private CmsJsonStore $json) {}

    public function settings(): array
    {
        return $this->json->read('site-settings', []);
    }

    public function saveSettings(array $data): void
    {
        $this->json->write('site-settings', $data);
    }

    public function menus(): array
    {
        return $this->json->read('menus', [
            'quick_links' => [],
            'main_nav' => [],
            'footer_columns' => [],
        ]);
    }

    public function saveMenus(array $data): void
    {
        $this->json->write('menus', $data);
    }

    public function home(): array
    {
        return $this->json->read('home-content', [
            'slides' => [],
            'ticker' => [],
        ]);
    }

    public function saveHome(array $data): void
    {
        $this->json->write('home-content', $data);
    }

    public function rankings(): array
    {
        return $this->json->read('rankings', []);
    }

    public function saveRankings(array $data): void
    {
        $this->json->write('rankings', array_values($data));
    }

    public function videos(): array
    {
        return $this->json->read('youtube-videos', []);
    }

    public function saveVideos(array $data): void
    {
        $this->json->write('youtube-videos', array_values($data));
    }

    public function albums(): array
    {
        return $this->json->read('convocation-albums', []);
    }

    public function saveAlbums(array $data): void
    {
        $this->json->write('convocation-albums', array_values($data));
    }

    public function album(string $id): ?array
    {
        foreach ($this->albums() as $album) {
            if ((string) ($album['id'] ?? '') === $id) {
                return $album;
            }
        }

        return null;
    }

    public static function href(string $url): string
    {
        $url = trim($url);
        if ($url === '' || $url === '#') {
            return '#';
        }
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, 'tel:') || str_starts_with($url, 'mailto:')) {
            return $url;
        }
        if (str_starts_with($url, '#')) {
            return $url;
        }

        return url('/'.ltrim($url, '/'));
    }

    public static function isExternal(array $item): bool
    {
        if (! empty($item['external'])) {
            return true;
        }
        $url = (string) ($item['url'] ?? '');

        return str_starts_with($url, 'http://') || str_starts_with($url, 'https://');
    }
}
