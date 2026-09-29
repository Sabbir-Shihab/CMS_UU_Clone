<?php

namespace App\Console\Commands;

use App\Support\PageRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportStaticPages extends Command
{
    protected $signature = 'cms:import {--force : Overwrite existing pages}';

    protected $description = 'Import static HTML files into the CMS page store';

    public function handle(PageRepository $pages): int
    {
        $root = config('cms.static_html_path');
        $files = glob($root.'/*.html') ?: [];
        $imported = 0;

        foreach ($files as $file) {
            $slug = basename($file, '.html');
            if ($slug === 'index') {
                $slug = 'home';
            }

            $html = file_get_contents($file);
            if ($html === false) {
                continue;
            }

            $title = 'Uttara University';
            if (preg_match('/<title>(.*?)<\/title>/si', $html, $m)) {
                $title = html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }

            $body = $this->extractBody($html);
            if ($body === '') {
                $this->warn('Skipped empty body: '.$slug);

                continue;
            }

            $body = $this->rewriteLinks($body);
            $menuKey = $this->menuKey($html, $slug);

            if (! $this->option('force') && $pages->find($slug)) {
                continue;
            }

            $existing = $pages->find($slug);
            $pages->upsert([
                'id' => $existing['id'] ?? (string) Str::uuid(),
                'slug' => $slug,
                'title' => $title,
                'menu_key' => $menuKey,
                'body' => $body,
            ]);
            $imported++;
        }

        $this->info("Imported {$imported} pages.");

        return self::SUCCESS;
    }

    private function extractBody(string $html): string
    {
        if (preg_match('/<div data-header><\/div>([\s\S]*?)<div data-footer>/', $html, $m)) {
            return trim($m[1]);
        }

        if (preg_match('/<main[\s\S]*?<\/main>/i', $html, $m)) {
            return trim($m[0]);
        }

        return '';
    }

    private function rewriteLinks(string $body): string
    {
        $body = str_replace('href="index.html"', 'href="/"', $body);

        return preg_replace('/href="([a-z0-9\-]+)\.html"/i', 'href="/$1"', $body) ?? $body;
    }

    private function menuKey(string $html, string $slug): string
    {
        if (preg_match('/data-page="([^"]+)"/', $html, $m)) {
            return $m[1];
        }

        return $slug;
    }
}
