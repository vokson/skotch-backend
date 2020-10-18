<?php

return [
    'default' => env('FILESYSTEM_DRIVER'),
    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],
    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

    /* Directory where zip archives are stored */
    'archiveStoragePath' => storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'zip'),
    'mergedPdfPath' => 'log_file_storage' . DIRECTORY_SEPARATOR . 'PDF_MERGE_FILES',
    'databaseBackupPath' => storage_path('app' . DIRECTORY_SEPARATOR . 'database_backup')

];
