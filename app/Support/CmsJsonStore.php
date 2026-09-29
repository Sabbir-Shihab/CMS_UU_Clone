<?php

namespace App\Support;

class CmsJsonStore
{
    public function path(string $name): string
    {
        return storage_path('app/cms/'.$name.'.json');
    }

    public function read(string $name, mixed $default = []): mixed
    {
        $path = $this->path($name);
        if (! is_file($path)) {
            return $default;
        }
        $data = json_decode((string) file_get_contents($path), true);

        return $data === null ? $default : $data;
    }

    public function write(string $name, mixed $data): void
    {
        $path = $this->path($name);
        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents(
            $path,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
    }
}
