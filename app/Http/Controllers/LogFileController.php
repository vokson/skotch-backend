<?php

namespace App\Http\Controllers;

use App\Models\UploadedFile;
use Illuminate\Http\Request;
use App\Models\Log;
use App\Http\Controllers\FeedbackController As Feedback;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class LogFileController extends Controller
{
    public function upload(Request $request)
    {

        $log_id = null;
        if ($request->has('log_id')) {

            if (!Log::where('id', '=', $request->input('log_id'))->exists()) {
                return Feedback::getFeedback(604);
            } else {
                $log_id = $request->input('log_id');
            }
        }


        if (!$request->hasFile('log_file')) {
            return Feedback::getFeedback(601);
        };

        if (!$request->file('log_file')->isValid()) {
            return Feedback::getFeedback(602);
        }

        if (!$request->has('uin')) {
            return Feedback::getFeedback(605);
        }

        try {

            $path = Storage::putFile(
                'log_file_storage' . DIRECTORY_SEPARATOR . 'FILES' . DIRECTORY_SEPARATOR .
                $this->createFolderByNumber($log_id) . DIRECTORY_SEPARATOR . $log_id,
                $request->file('log_file')
            );

        } catch (QueryException $e) {

            return Feedback::getFeedback(607);
        }


        if ($path === false) {
            return Feedback::getFeedback(606);
        }

        $file = new UploadedFile();
        $file->original_name = $request->file('log_file')->getClientOriginalName();
        $file->size = $request->file('log_file')->getSize();
        $file->log = $log_id;
        $file->server_name = $path;
        $file->uin = $request->input('uin');
        $file->save();

        // Изменяем is_attachment_exist для записи
        $log = Log::find($log_id);
        $log->is_attachment_exist = true;
        $log->save();

        return Feedback::getFeedback(0, [
            'id' => $file->id,
            'uin' => $file->uin,
            'log_id' => $file->log
        ]);
    }

    protected function createFolderByNumber($number)
    {
        $min = intdiv($number, 1000) * 1000 + 1;
        $max = (intdiv($number, 1000) + 1) * 1000;

        if ($number % 1000 == 0) {
            $min = $min - 1000;
            $max = $max - 1000;
        }

//        echo "MIN = " . $min . '<br/>';
//        echo "MAX = " . $max . '<br/>';

        return sprintf("%05d-%05d", $min, $max);
    }

    public function get(Request $request)
    {
        $log_id = trim($request->input('log_id', ''));

        $items = DB::table('uploaded_files')
            ->where('log', '=', $log_id)
            ->select(['id', 'uin', 'original_name', 'size'])
            ->orderBy('id', 'asc')
            ->get();


        return Feedback::getFeedback(0, [
            'items' => $items->toArray(),
        ]);


    }

    public function delete(Request $request)
    {
        $file_id = null;
        if ($request->has('id')) {

            if (!UploadedFile::where('id', '=', $request->input('id'))->exists()) {
                return Feedback::getFeedback(604);
            } else {
                $file_id = $request->input('id');
            }
        }

        $file = UploadedFile::find($file_id);

        try {

            Storage::delete($file->server_name);

        } catch (QueryException $e) {

            return Feedback::getFeedback(603);
        }

        $uin = $file->uin;
        $log_id = $file->log;

        // Удаляем файл
        $file->delete();

        // Проверяем есть ли еще файлы у данной записи
        // Если нет, изменяем is_attachment_exist для записи
        if (UploadedFile::where('log', $log_id)->count() <= 0) {
            $log = Log::find($log_id);
            $log->is_attachment_exist = false;
            $log->save();
        }

        return Feedback::getFeedback(0, [
//            'log_id' => $log_id,
            'uin' => $uin
        ]);
    }

    public function download(Request $request)
    {
        $file_id = null;
        if ($request->has('id')) {

            if (!UploadedFile::where('id', '=', $request->input('id'))->exists()) {
                return Feedback::getFeedback(604);
            } else {
                $file_id = $request->input('id');
            }
        }

        $file = UploadedFile::find($file_id);
        // Проверяем существует ли файл на диске
        if (!file_exists(storage_path("app/" . $file->server_name))) {
            return response(Feedback::getFeedback(601), 500);
        }

        $headers = array(
            'Content-Type' => 'application/octet-stream',
            'Access-Control-Expose-Headers' => 'Content-Filename',
            'Content-Filename' => rawurlencode($file->original_name)
        );

        return response()->download(storage_path("app/" . $file->server_name), "", $headers);
    }



    public function downloadAll(Request $request)
    {
        $log_id = intval($request->input('id', 0));
        $files = UploadedFile::where('log', $log_id)->get();

        $filesForZipArchive = [];
        foreach ($files as $file) {
            $filesForZipArchive[] = [
                'absolute_path' => storage_path("app/" . $file->server_name),
                'filename' => $file->original_name
            ];
        }

        return ZipArchiveController::download($filesForZipArchive);
    }

//    public function downloadAll(Request $request)
//    {
//        $this->cleanOldArchives();
//
//        $log_id = intval($request->input('id', 0));
//        $files = UploadedFile::where('log', $log_id)->get();
//
//        $fileForZipArchive = [];
//        foreach ($files as $file) {
//            $fileForZipArchive[] = [
//                'absolute_path' => storage_path("app/" . $file->server_name),
//                'filename' => $file->original_name
//            ];
//        }
//
//        $archiveName =  uniqid() . '.zip';
//
//        $zipPath = config('filesystems.zip_storage_path') . DIRECTORY_SEPARATOR . $archiveName;
//
//        set_time_limit(Settings::take('ARCHIVE_CREATION_TIME') );
//
//        if ($this->createArchive($fileForZipArchive, $zipPath) === FALSE) {
//            return Feedback::getFeedback(608);
//        }
//
//       $headers = array(
//            'Content-Type' => 'application/octet-stream',
//            'Access-Control-Expose-Headers' => 'Content-Filename',
//            'Content-Filename' => $archiveName
//        );
//
//        return response()->download($zipPath, "", $headers);
//    }

    public function clean()
    {
        $files = UploadedFile::all();

        foreach ($files as $file) {

            if (!Log::where('id', '=', $file->log)->exists()) {
                Storage::delete($file->server_name);
                $file->delete();
            }

        }

        return Feedback::getFeedback();
    }

//    public function createArchive($files, $zipPath)
//    {
//
//        $zip = new ZipArchive();
//
//        if ($zip->open($zipPath, ZIPARCHIVE::CREATE) === TRUE) {
//
//            foreach ($files as $file) {
//                if (file_exists($file['absolute_path'])) {
//                    $zip->addFile($file['absolute_path'], $file['filename']);
//                }
//            }
//
//            if ($zip->numFiles == 0) return FALSE;
//
//            return ($zip->status == ZipArchive::ER_OK);
//        }
//
//        return FALSE;
//    }
//
//    public function cleanOldArchives()
//    {
//        foreach (glob(config('filesystems.zip_storage_path'). DIRECTORY_SEPARATOR  . '*') as $fileName) {
//            if ( (microtime(true) - filectime($fileName) > Settings::take('ARCHIVE_STORAGE_TIME') )) {
//                unlink($fileName);
//            }
//        }
//    }

}
