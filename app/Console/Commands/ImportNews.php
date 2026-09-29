<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportNews extends Command
{
    protected $signature = 'news:import';

    protected $description = 'Download official news and events (including images) into the MySQL database';

    public function handle(): int
    {
        $script = base_path('scripts/import_news_db.py');
        $this->info('Importing news and events into MySQL database …');
        passthru('python3 '.escapeshellarg($script), $code);

        return $code === 0 ? self::SUCCESS : self::FAILURE;
    }
}
