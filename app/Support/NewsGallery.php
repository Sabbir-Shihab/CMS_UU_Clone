<?php

namespace App\Support;

class NewsGallery
{
    /**
     * Lay photos out like a news article: two side by side, then one full width, repeating.
     * A leftover single photo spans the full width.
     *
     * @param  array<int, string>  $paths
     * @return array<int, array<int, string>>
     */
    public static function rows(array $paths): array
    {
        $paths = array_values(array_filter(
            $paths,
            fn (mixed $path): bool => is_string($path) && trim($path) !== ''
        ));

        $rows = [];
        $index = 0;
        $total = count($paths);

        while ($index < $total) {
            $remaining = $total - $index;

            if ($remaining === 1) {
                $rows[] = [$paths[$index]];
                $index += 1;

                continue;
            }

            if ($remaining === 2) {
                $rows[] = [$paths[$index], $paths[$index + 1]];
                $index += 2;

                continue;
            }

            $rows[] = [$paths[$index], $paths[$index + 1]];
            $rows[] = [$paths[$index + 2]];
            $index += 3;
        }

        return $rows;
    }

    /**
     * @return array<int, string>
     */
    public static function decode(?string $json): array
    {
        $decoded = json_decode((string) $json, true);
        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_filter(
            $decoded,
            fn (mixed $path): bool => is_string($path) && trim($path) !== ''
        ));
    }
}
