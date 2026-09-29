<?php

namespace App\Console\Commands;

use App\Support\SqliteStore;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class LocalizeRemoteAssets extends Command
{
    protected $signature = 'assets:localize
                            {--dry-run : List remote assets without downloading or rewriting}
                            {--skip-download : Only rewrite references (assume files already exist)}
                            {--concurrency=12 : Parallel download batch size}';

    protected $description = 'Download remote banners/images into public/ and rewrite references to local paths';

    private const HOST_PATTERN = '(?:https?:)?//(?:www\.)?uttara\.ac\.bd';

    public function handle(SqliteStore $db): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $skipDownload = (bool) $this->option('skip-download');
        $concurrency = max(1, (int) $this->option('concurrency'));

        $urls = $this->collectRemoteUrls($db);
        $this->info('Found '.count($urls).' unique remote media URLs.');

        if ($dryRun) {
            foreach (array_slice($urls, 0, 30) as $url) {
                $this->line('  '.$url);
            }
            if (count($urls) > 30) {
                $this->line('  … and '.(count($urls) - 30).' more');
            }

            return self::SUCCESS;
        }

        $map = [];
        if (! $skipDownload) {
            $map = $this->downloadAll($urls, $concurrency);
        } else {
            foreach ($urls as $url) {
                $local = $this->localPathFor($url);
                if ($local !== null) {
                    $map[$url] = $local;
                }
            }
        }

        $youtubeMap = $this->localizeYoutubeThumbs($concurrency);
        $map = array_merge($map, $youtubeMap);

        $rewritten = $this->rewriteFiles($map);
        $dbUpdated = $this->rewriteDatabase($db, $map);

        $this->info("Downloaded/mapped: {$this->countExisting($map)} local files.");
        $this->info("Rewrote {$rewritten} files and {$dbUpdated} database rows.");

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function collectRemoteUrls(SqliteStore $db): array
    {
        $urls = [];

        foreach ($this->scanFiles() as $path) {
            $text = @file_get_contents($path);
            if ($text === false || $text === '') {
                continue;
            }
            foreach ($this->extractUttaraUrls($text) as $url) {
                $urls[$url] = true;
            }
        }

        foreach (['news_posts' => ['content', 'image_path'], 'notice_posts' => ['content', 'image_path'], 'teachers' => ['photo_path']] as $table => $columns) {
            foreach ($columns as $column) {
                $rows = $db->select("SELECT {$column} AS value FROM {$table} WHERE {$column} IS NOT NULL AND {$column} != ''");
                foreach ($rows as $row) {
                    foreach ($this->extractUttaraUrls((string) $row['value']) as $url) {
                        $urls[$url] = true;
                    }
                    $value = trim((string) $row['value']);
                    if (preg_match('#^https?://#i', $value) && $this->looksLikeMediaPath(parse_url($value, PHP_URL_PATH) ?? '')) {
                        $urls[$this->normalizeUrl($value)] = true;
                    }
                }
            }
        }

        $sorted = array_keys($urls);
        sort($sorted);

        return $sorted;
    }

    /**
     * @return list<string>
     */
    private function scanFiles(): array
    {
        $files = [];

        foreach (glob(storage_path('app/cms/*.json')) ?: [] as $file) {
            $files[] = $file;
        }

        foreach ([public_path('css/style.css'), public_path('js/main.js')] as $file) {
            if (is_file($file)) {
                $files[] = $file;
            }
        }

        $views = resource_path('views');
        if (is_dir($views)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($views));
            /** @var SplFileInfo $info */
            foreach ($iterator as $info) {
                if ($info->isFile() && str_ends_with($info->getFilename(), '.blade.php')) {
                    $files[] = $info->getPathname();
                }
            }
        }

        return $files;
    }

    /**
     * @return list<string>
     */
    private function extractUttaraUrls(string $text): array
    {
        $found = [];
        $pattern = '#'.self::HOST_PATTERN.'(/[^\"\'\s\)<>]+)#i';
        if (! preg_match_all($pattern, $text, $matches)) {
            return [];
        }

        foreach ($matches[1] as $path) {
            $path = explode('?', $path, 2)[0];
            $path = explode('#', $path, 2)[0];
            if (! $this->looksLikeMediaPath($path)) {
                continue;
            }
            $found[] = $this->normalizeUrl('https://www.uttara.ac.bd'.$path);
        }

        return array_values(array_unique($found));
    }

    private function looksLikeMediaPath(string $path): bool
    {
        $path = strtolower($path);
        if (str_contains($path, '/wp-content/') || str_contains($path, '/api/image/')) {
            return true;
        }

        return (bool) preg_match('/\.(webp|jpe?g|png|gif|svg|avif|pdf|mp4|ico)$/i', $path);
    }

    private function normalizeUrl(string $url): string
    {
        if (str_starts_with($url, '//')) {
            $url = 'https:'.$url;
        }
        $parts = parse_url($url);
        $path = $parts['path'] ?? '/';
        $path = '/'.ltrim(preg_replace('#/+#', '/', $path) ?? $path, '/');

        return 'https://www.uttara.ac.bd'.$path;
    }

    private function localPathFor(string $url): ?string
    {
        $path = parse_url($this->normalizeUrl($url), PHP_URL_PATH);
        if (! is_string($path) || $path === '' || $path === '/') {
            return null;
        }

        return '/'.ltrim(preg_replace('#/+#', '/', $path) ?? $path, '/');
    }

    /**
     * @param  list<string>  $urls
     * @return array<string, string> remote URL => local public path
     */
    private function downloadAll(array $urls, int $concurrency): array
    {
        $map = [];
        $jobs = [];
        $skipped = 0;
        $fail = 0;

        foreach ($urls as $url) {
            $local = $this->localPathFor($url);
            if ($local === null) {
                $fail++;

                continue;
            }

            $absolute = public_path(ltrim($local, '/'));
            if (is_file($absolute) && filesize($absolute) > 0) {
                $map[$url] = $local;
                $skipped++;

                continue;
            }

            $jobs[] = [$url, $local, $absolute];
        }

        $this->info('Already present: '.$skipped.'; to download: '.count($jobs));

        if ($jobs === []) {
            return $map;
        }

        $manifest = storage_path('app/localize-download-manifest.tsv');
        $fh = fopen($manifest, 'w');
        foreach ($jobs as [$url, $local, $absolute]) {
            $dir = dirname($absolute);
            if (! is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            fwrite($fh, $url."\t".$absolute."\n");
        }
        fclose($fh);

        $script = <<<'BASH'
set -euo pipefail
manifest="$1"
concurrency="$2"
ok=0
fail=0
download_one() {
  url="$1"
  dest="$2"
  tmp="${dest}.part"
  if curl -fsSL --connect-timeout 15 --max-time 120 -A "Mozilla/5.0 (compatible; UU-Localizer/1.0)" -o "$tmp" "$url"; then
    if [ -s "$tmp" ]; then
      mv "$tmp" "$dest"
      echo OK
      return 0
    fi
  fi
  rm -f "$tmp"
  echo FAIL "$url" >&2
  return 1
}
export -f download_one
while IFS=$'\t' read -r url dest; do
  printf '%s\0%s\0' "$url" "$dest"
done < "$manifest" | xargs -0 -n 2 -P "$concurrency" bash -c 'download_one "$1" "$2"' _
BASH;

        $scriptFile = storage_path('app/localize-download.sh');
        file_put_contents($scriptFile, $script);
        chmod($scriptFile, 0755);

        $bar = $this->output->createProgressBar(count($jobs));
        $bar->start();

        // Run in batches so the progress bar advances.
        $batchSize = max($concurrency * 4, 32);
        $chunks = array_chunk($jobs, $batchSize);
        $ok = 0;
        foreach ($chunks as $chunk) {
            $chunkManifest = storage_path('app/localize-download-chunk.tsv');
            $fh = fopen($chunkManifest, 'w');
            foreach ($chunk as [$url, $local, $absolute]) {
                fwrite($fh, $url."\t".$absolute."\n");
            }
            fclose($fh);

            $cmd = 'bash '.escapeshellarg($scriptFile).' '.escapeshellarg($chunkManifest).' '.escapeshellarg((string) $concurrency);
            exec($cmd.' 2>'.escapeshellarg(storage_path('app/localize-download.err')), $output, $code);

            foreach ($chunk as [$url, $local, $absolute]) {
                if (is_file($absolute) && filesize($absolute) > 0) {
                    $map[$url] = $local;
                    $ok++;
                } else {
                    $fail++;
                    $this->warn('Failed: '.$url);
                }
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("Downloads: {$ok} new, {$skipped} already present, {$fail} failed.");

        return $map;
    }

    /**
     * Kept for YouTube thumbs (small batches).
     *
     * @param  array<string, string>  $pending
     * @param  array<string, string>  $map
     * @return array{0: int, 1: int}
     */
    private function flushDownloads(array $pending, array &$map): array
    {
        $ok = 0;
        $fail = 0;

        foreach ($pending as $url => $local) {
            $absolute = public_path(ltrim($local, '/'));
            $dir = dirname($absolute);
            if (! is_dir($dir)) {
                mkdir($dir, 0775, true);
            }

            $tmp = $absolute.'.part';
            $cmd = sprintf(
                'curl -fsSL --connect-timeout 15 --max-time 120 -A %s -o %s %s',
                escapeshellarg('Mozilla/5.0 (compatible; UU-Localizer/1.0)'),
                escapeshellarg($tmp),
                escapeshellarg($url)
            );
            exec($cmd.' 2>/dev/null', $output, $code);

            if ($code === 0 && is_file($tmp) && filesize($tmp) > 0) {
                rename($tmp, $absolute);
                $map[$url] = $local;
                $ok++;
            } else {
                if (is_file($tmp)) {
                    @unlink($tmp);
                }
                $fail++;
                $this->warn('Failed: '.$url);
            }
        }

        return [$ok, $fail];
    }

    /**
     * @return array<string, string>
     */
    private function localizeYoutubeThumbs(int $concurrency): array
    {
        $ids = [];
        $videosPath = storage_path('app/cms/youtube-videos.json');
        if (is_file($videosPath)) {
            $videos = json_decode((string) file_get_contents($videosPath), true) ?: [];
            foreach ($videos as $video) {
                $id = trim((string) ($video['id'] ?? ''));
                if ($id !== '') {
                    $ids[$id] = true;
                }
            }
        }

        foreach ($this->scanFiles() as $path) {
            $text = @file_get_contents($path) ?: '';
            if (preg_match_all('#i\.ytimg\.com/vi/([^/]+)/#', $text, $matches)) {
                foreach ($matches[1] as $id) {
                    $ids[$id] = true;
                }
            }
        }

        $map = [];
        $pending = [];
        foreach (array_keys($ids) as $id) {
            $local = '/uploads/youtube/'.$id.'.jpg';
            $absolute = public_path(ltrim($local, '/'));
            $remote = 'https://i.ytimg.com/vi/'.$id.'/hqdefault.jpg';
            if (is_file($absolute) && filesize($absolute) > 0) {
                $map[$remote] = $local;

                continue;
            }
            $pending[$remote] = $local;
        }

        if ($pending !== []) {
            $this->info('Downloading '.count($pending).' YouTube thumbnails…');
            $this->flushDownloads($pending, $map);
        }

        return $map;
    }

    /**
     * @param  array<string, string>  $map
     */
    private function rewriteFiles(array $map): int
    {
        $count = 0;
        foreach ($this->scanFiles() as $path) {
            $original = @file_get_contents($path);
            if ($original === false || $original === '') {
                continue;
            }
            $updated = $this->rewriteText($original, $map);
            if ($updated !== $original) {
                file_put_contents($path, $updated);
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param  array<string, string>  $map
     */
    private function rewriteDatabase(SqliteStore $db, array $map): int
    {
        $updated = 0;

        foreach (['news_posts' => ['content', 'image_path'], 'notice_posts' => ['content', 'image_path'], 'teachers' => ['photo_path']] as $table => $columns) {
            $rows = $db->select('SELECT id, '.implode(', ', $columns)." FROM {$table}");
            foreach ($rows as $row) {
                $changes = [];
                foreach ($columns as $column) {
                    $value = (string) ($row[$column] ?? '');
                    if ($value === '') {
                        continue;
                    }
                    $newValue = $this->rewriteText($value, $map);
                    // Exact remote photo URLs that were mapped
                    if (isset($map[$this->normalizeUrl($value)])) {
                        $newValue = $map[$this->normalizeUrl($value)];
                    } elseif (preg_match('#^https?://#i', $value)) {
                        $normalized = $this->normalizeUrl($value);
                        if (isset($map[$normalized])) {
                            $newValue = $map[$normalized];
                        }
                    }
                    if ($newValue !== $value) {
                        $changes[$column] = $newValue;
                    }
                }
                if ($changes === []) {
                    continue;
                }
                $sets = [];
                $params = [];
                foreach ($changes as $column => $value) {
                    $sets[] = "{$column} = ?";
                    $params[] = $value;
                }
                $params[] = $row['id'];
                $db->execute('UPDATE '.$table.' SET '.implode(', ', $sets).' WHERE id = ?', $params);
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * @param  array<string, string>  $map
     */
    private function rewriteText(string $text, array $map): string
    {
        // Prefer exact mapped replacements (handles YouTube thumbs and odd paths).
        if ($map !== []) {
            uksort($map, fn (string $a, string $b): int => strlen($b) <=> strlen($a));
            foreach ($map as $remote => $local) {
                $variants = array_unique([
                    $remote,
                    Str::replaceFirst('https://www.', 'https://', $remote),
                    Str::replaceFirst('https://', 'http://', $remote),
                    Str::replaceFirst('https:', '', $remote),
                ]);
                foreach ($variants as $variant) {
                    if ($variant !== '' && str_contains($text, $variant)) {
                        $text = str_replace($variant, $local, $text);
                    }
                }
            }
        }

        // Generic host stripping for remaining uttara media paths.
        $text = preg_replace(
            '#'.self::HOST_PATTERN.'(/(?:wp-content|api/image)/[^\"\'\s\)<>]+)#i',
            '$1',
            $text
        ) ?? $text;

        // JS IMG base used for logos/theme images.
        $text = preg_replace(
            '#const IMG = "https://www\.uttara\.ac\.bd";#',
            'const IMG = "";',
            $text
        ) ?? $text;

        // YouTube thumbnails still pointing at i.ytimg.com
        $text = preg_replace_callback(
            '#https://i\.ytimg\.com/vi/([^/]+)/hqdefault\.jpg#',
            function (array $m): string {
                $local = '/uploads/youtube/'.$m[1].'.jpg';
                if (is_file(public_path(ltrim($local, '/')))) {
                    return $local;
                }

                return $m[0];
            },
            $text
        ) ?? $text;

        return $text;
    }

    /**
     * @param  array<string, string>  $map
     */
    private function countExisting(array $map): int
    {
        $n = 0;
        foreach ($map as $local) {
            if (is_file(public_path(ltrim($local, '/')))) {
                $n++;
            }
        }

        return $n;
    }
}
