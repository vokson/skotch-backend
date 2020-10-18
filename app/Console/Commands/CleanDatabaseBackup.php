<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Exception;

class CleanDatabaseBackup extends Command
{
    protected $signature = 'database:backup_clean';
    protected $description = 'Clean too old backups of database';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $directory = config('filesystems.database_backup_path');
        $scanned_directory = array_diff(scandir($directory), array('..', '.'));

        foreach ($scanned_directory as $filename) {
            $path = $directory . '/' . $filename;

            if (file_exists($path) && (time() - filemtime($path) > intval(config('database.backup_lifetime')) )) {

                try {
                    $this->line('Deleting ' . $path);
                    unlink($path);

                } catch (Exception $e) {
                    $this->error('Error during operation. See details in laravel.log');
                    Log::error($e->getMessage());
                    return 1;
                }

            }
        }

        $this->info('Success!');

        return 0;
    }
}
