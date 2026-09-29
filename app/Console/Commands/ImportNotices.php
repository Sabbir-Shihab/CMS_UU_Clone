<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportNotices extends Command
{
    protected $signature = 'notices:import';

    protected $description = 'Download official notices into the MySQL database';

    public function handle(): int
    {
        $script = base_path('scripts/import_notices_db.py');
        $this->info('Importing notices into MySQL database …');
        passthru('python3 '.escapeshellarg($script), $code);

        return $code === 0 ? self::SUCCESS : self::FAILURE;
    }
}
