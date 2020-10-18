<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class InitStorageFolders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:init_folders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Init project storage folders';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $folders = [
            'database_backup_path',
            'check_files_path',
            'record_files_path',
            'pdf_merge_files_path',
            'sender_files_path',
            'temporary_files_path',
            'zip_storage_path'
        ];

        foreach ($folders as $folder) {
            $path = config('filesystems.' . $folder);

            if (!file_exists($path)) {

                $this->info('Creating ' . $path . '  ...');
                try {
                    mkdir($path, 0777, true);
                    $this->info('Success!');

                } catch (Exception $e) {
                    $this->error('Error during operation. See details in laravel.log');
                    Log::error($e->getMessage());
                    return 1;
                }

            } else {
                $this->line($path . ' already exists');
            }
        }



        return 0;
    }
}
