<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class DatabaseBackup extends Command
{
    protected $signature = 'database:backup';
    protected $description = 'Copy database.sqlite file into another folder';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $filename = Carbon::now()->toDateTimeString() . ".sqlite";
        $filename = str_replace(' ', '_', $filename);
        $filename = str_replace(':', '_', $filename);

        $path = config('filesystems.databaseBackupPath') . DIRECTORY_SEPARATOR . $filename;

        try {
            $this->line('Copying ' . database_path('database.sqlite') . ' to ' . $path);
            File::copy(database_path('database.sqlite'), $path);
            $this->info('Success!');

        } catch (Exception $e) {
            $this->error('Error during operation. See details in laravel.log');
            Log::error($e->getMessage());
            return 1;
        }

        return 0;
    }
}
