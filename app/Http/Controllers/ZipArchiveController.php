<?php

namespace App\Http\Controllers;

use App\Http\Controllers\FeedbackController As Feedback;
use ZipArchive;
use App\Http\Controllers\SettingsController as Settings;

class ZipArchiveController extends Controller
{
    // $fileForZipArchive - Array with fields
    // absolute_path
    // filename
    public static function download($fileForZipArchive)
    {
        self::cleanOldArchives();

        $archiveName =  uniqid() . '.zip';

        $zipPath = config('filesystems.archiveStoragePath') . DIRECTORY_SEPARATOR . $archiveName;

        set_time_limit(Settings::take('ARCHIVE_CREATION_TIME') );

        if (self::createArchive($fileForZipArchive, $zipPath) === FALSE) {
            return response(Feedback::getFeedback(608), 500);
        }

       $headers = array(
            'Content-Type' => 'application/octet-stream',
            'Access-Control-Expose-Headers' => 'Content-Filename',
            'Content-Filename' => $archiveName
        );

        return response()->download($zipPath, "", $headers);
    }


    public static function createArchive($files, $zipPath)
    {

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZIPARCHIVE::CREATE) === TRUE) {

            foreach ($files as $file) {
                if (file_exists($file['absolute_path'])) {
                    $zip->addFile($file['absolute_path'], $file['filename']);
                }
            }

            if ($zip->numFiles == 0) return FALSE;

            return ($zip->status == ZipArchive::ER_OK);
        }

        return FALSE;
    }

    public static function cleanOldArchives()
    {
        foreach (glob(config('filesystems.archiveStoragePath'). DIRECTORY_SEPARATOR  . '*') as $fileName) {
            if ( (microtime(true) - filectime($fileName) > Settings::take('ARCHIVE_STORAGE_TIME') )) {
                unlink($fileName);
            }
        }
    }

}
