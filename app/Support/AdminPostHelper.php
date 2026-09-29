<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class AdminPostHelper
{
    public static function slugify(string $title): string
    {
        $slug = Str::slug($title);
        if ($slug !== '') {
            return $slug;
        }

        $fallback = preg_replace('/\s+/u', '-', trim($title)) ?: 'post';
        $fallback = preg_replace('/[^\p{L}\p{N}\-_]+/u', '', $fallback) ?: 'post';

        return mb_strtolower($fallback);
    }

    public static function uniqueSlug(SqliteStore $db, string $table, string $title, ?int $ignoreId = null): string
    {
        $base = self::slugify($title);
        $slug = $base;
        $i = 2;
        while (true) {
            $sql = "SELECT id FROM {$table} WHERE slug = ?";
            $params = [$slug];
            if ($ignoreId) {
                $sql .= ' AND id != ?';
                $params[] = $ignoreId;
            }
            $sql .= ' LIMIT 1';
            $rows = $db->select($sql, $params);
            if (! $rows) {
                return $slug;
            }
            $slug = $base.'-'.$i;
            $i++;
        }
    }

    /**
     * @return array{display_date: string, published_at: string, day_label: string, month_label: string}
     */
    public static function datesFromInput(?string $date): array
    {
        $carbon = $date ? Carbon::parse($date) : now();

        return [
            'display_date' => $carbon->format('j F, Y'),
            'published_at' => $carbon->format('Y-m-d H:i:s'),
            'day_label' => $carbon->format('j'),
            'month_label' => $carbon->format('M'),
        ];
    }

    public static function dateInputValue(?string $publishedAt, ?string $displayDate = null): string
    {
        if ($publishedAt) {
            try {
                return Carbon::parse($publishedAt)->format('Y-m-d');
            } catch (\Throwable) {
                // fall through
            }
        }
        if ($displayDate) {
            try {
                return Carbon::parse($displayDate)->format('Y-m-d');
            } catch (\Throwable) {
                // fall through
            }
        }

        return now()->format('Y-m-d');
    }

    public static function storeCover(UploadedFile $file, string $kind, string $slug, ?string $filename = null): string
    {
        $dir = public_path('uploads/'.$kind.'/'.$slug);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $name = $filename ? preg_replace('/[^a-zA-Z0-9._-]/', '', $filename) : 'cover.'.$ext;
        if (! str_contains($name, '.')) {
            $name .= '.'.$ext;
        }
        $file->move($dir, $name);

        return '/uploads/'.$kind.'/'.$slug.'/'.$name;
    }

    public static function publicPath(?string $path): string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }
}
