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
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
        ],
    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

    /* Directory where zip archives are stored */
    'zip_storage_path' => storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'zip'),
//    'mergedPdfPath' => 'log_file_storage' . DIRECTORY_SEPARATOR . 'PDF_MERGE_FILES',
    'database_backup_path' => storage_path('app' . DIRECTORY_SEPARATOR . 'database_backup'),
    'check_files_path' => storage_path('app' . DIRECTORY_SEPARATOR . 'log_file_storage' . DIRECTORY_SEPARATOR . 'CHECKED_FILES'),
    'record_files_path' => storage_path('app' . DIRECTORY_SEPARATOR . 'log_file_storage' . DIRECTORY_SEPARATOR . 'FILES'),
    'pdf_merge_files_path' => storage_path('app' . DIRECTORY_SEPARATOR . 'log_file_storage' . DIRECTORY_SEPARATOR . 'PDF_MERGE_FILES'),
    'sender_files_path' => storage_path('app' . DIRECTORY_SEPARATOR . 'log_file_storage' . DIRECTORY_SEPARATOR . 'SENDER_FILES'),
    'temporary_files_path' => storage_path('app' . DIRECTORY_SEPARATOR . 'log_file_storage' . DIRECTORY_SEPARATOR . 'TEMPORARY_FILES'),


];
