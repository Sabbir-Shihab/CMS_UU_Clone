<?php

namespace App\Http\Controllers;

use App\Support\NewsRepository;
use App\Support\NoticeRepository;
use App\Support\PageRepository;
use App\Support\SiteContent;

class SiteController extends Controller
{
    public function __construct(
        private PageRepository $pages,
        private NewsRepository $news,
        private NoticeRepository $notices,
        private SiteContent $content,
    ) {}

    public function home()
    {
        if (request()->filled('s')) {
            return $this->search();
        }

        $page = $this->pages->findPublished('home') ?? $this->pages->findPublished('index');
        abort_unless($page, 404);

        $home = $this->content->home();
        $latest = $this->news->latest(9);

        if (! empty($home['slides'])) {
            $page['body'] = preg_replace(
                '/<div class="hero-slider">.*?<\/div>\s*(?=<section class="ticker"|<section class="about-section")/s',
                view('site.partials.home-slider', ['slides' => $home['slides']])->render()."\n\n    ",
                $page['body'],
                1
            );
        }

        if (! empty($home['ticker'])) {
            $page['body'] = preg_replace(
                '/<section class="ticker">.*?<\/section>/s',
                view('site.partials.home-ticker', ['ticker' => $home['ticker']])->render(),
                $page['body'],
                1
            );
        }

        if ($latest) {
            $page['body'] = preg_replace(
                '/<section class="news-block">\s*<div class="container">\s*<h3 class="news-top-title">Latest News<\/h3>.*?<\/section>/s',
                view('site.partials.home-latest-news', ['items' => $latest])->render(),
                $page['body'],
                1
            );
        }

        $page['body'] = preg_replace(
            '/<section class="latest-news-section">.*?<\/section>/s',
            view('site.partials.home-notices', [
                'notices' => $this->notices->latest(5),
                'news' => array_slice($latest ?: [], 0, 5),
            ])->render(),
            $page['body'],
            1
        );

        $page['body'] = preg_replace(
            '/<section class="news-block">\s*<div class="container">\s*<h3 class="news-top-title"[^>]*>Recognitions and Rankings at a Glance<\/h3>.*?<\/section>/s',
            view('site.partials.home-rankings', ['logos' => $this->rankingLogos()])->render(),
            $page['body'],
            1
        );

        return view('site.show', ['page' => $page]);
    }

    public function search()
    {
        $query = trim((string) request('s', ''));
        $results = [];

        if ($query !== '') {
            foreach ($this->pages->search($query, 40) as $hit) {
                $results[] = [
                    'title' => $hit['title'],
                    'url' => url('/'.$hit['slug']),
                    'type' => $hit['type'],
                    'date' => '',
                ];
            }
            foreach ($this->news->search($query, 25) as $hit) {
                $results[] = [
                    'title' => $hit['title'],
                    'url' => url('/'.$hit['slug']),
                    'type' => $hit['type'],
                    'date' => $hit['date'] ?? '',
                ];
            }
            foreach ($this->notices->search($query, 25) as $hit) {
                $results[] = [
                    'title' => $hit['title'],
                    'url' => '/'.ltrim($hit['slug'], '/'),
                    'type' => $hit['type'],
                    'date' => $hit['date'] ?? '',
                ];
            }
        }

        $page = [
            'slug' => 'search',
            'title' => ($query !== '' ? $query : 'Search').' - Uttara University',
            'menu_key' => 'home',
            'body' => view('site.partials.search-results', [
                'query' => $query,
                'results' => $results,
            ])->render(),
        ];

        return view('site.show', ['page' => $page]);
    }

    /** @return array<int, array{src: string, alt: string}> */
    private function rankingLogos(): array
    {
        $logos = [];
        foreach ($this->content->rankings() as $item) {
            $src = (string) ($item['image'] ?? '');
            if ($src === '') {
                continue;
            }
            if (! str_starts_with($src, 'http')) {
                $src = asset(ltrim($src, '/'));
            }
            $logos[] = [
                'src' => $src,
                'alt' => (string) ($item['alt'] ?? 'Ranking'),
            ];
        }

        return $logos;
    }

    public function show(string $slug)
    {
        if (in_array($slug, ['admin', 'up'], true)) {
            abort(404);
        }

        if ($slug === 'convocations') {
            $slug = 'convocation';
        }

        if ($slug === 'alumni-announcements') {
            $slug = 'alumni';
        }

        if ($slug === 'notice') {
            $slug = 'notices';
        }

        $page = $this->news->articlePage($slug);
        if ($page) {
            return view('site.show', ['page' => $page]);
        }

        $page = $this->notices->articlePage($slug);
        if ($page) {
            return view('site.show', ['page' => $page]);
        }

        $page = $this->pages->findPublished($slug);
        if (! $page && $slug === 'news') {
            $page = [
                'slug' => 'news',
                'title' => 'News & Events - Uttara University',
                'menu_key' => 'news',
                'body' => '',
                'status' => 'published',
            ];
        }

        if (! $page && $slug === 'notices') {
            $page = [
                'slug' => 'notices',
                'title' => 'Notice - Uttara University',
                'menu_key' => 'notices',
                'body' => '',
                'status' => 'published',
            ];
        }

        if (! $page) {
            abort(404);
        }

        if ($slug === 'news') {
            $listing = $this->news->paginate((int) request('page', 1), 12);
            $page['title'] = 'News & Events - Uttara University';
            $page['menu_key'] = 'news';
            $page['body'] = view('site.partials.news-events', $listing)->render();
        }

        if ($slug === 'notices') {
            $listing = $this->notices->paginate((int) request('page', 1), 12);
            $page['title'] = 'Notice - Uttara University';
            $page['menu_key'] = 'notices';
            $page['body'] = view('site.partials.notices-list', $listing)->render();
        }

        if (in_array($slug, ['gallery', 'video-gallery'], true)) {
            $page['title'] = 'Video Gallery | Uttara University';
            $page['body'] = view('site.partials.video-gallery', [
                'videos' => $this->content->videos(),
            ])->render();
        }

        if ($slug === 'convocation') {
            $albumId = (string) request('album', '');
            if ($albumId !== '') {
                $album = $this->content->album($albumId);
                abort_unless($album, 404);
                $page['title'] = ($album['title'] ?? 'Convocation').' | Uttara University';
                $page['body'] = view('site.partials.convocation-album', ['album' => $album])->render();
            }
        }

        return view('site.show', ['page' => $page]);
    }
}
