<?php

namespace App\Http\Controllers;

use App\Models\SenderFile;
use App\Models\SenderFolder;
use Illuminate\Http\Request;
use App\Http\Controllers\FeedbackController As Feedback;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Check;
use Illuminate\Database\QueryException;

class SenderFileController extends Controller
{
    public function upload(Request $request)
    {

        $folder_id = null;
        if ($request->has('folder_id')) {

            if (!SenderFolder::where('id', '=', $request->input('folder_id'))->exists()) {
                return Feedback::getFeedback(604);
            } else {
                $folder_id = $request->input('folder_id');
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

        $originalNameOfFile = $request->file('log_file')->getClientOriginalName();

        if (!CheckController::validateNameOfNewFile($originalNameOfFile))  {
            return Feedback::getFeedback(609);
        }

        $path_parts = pathinfo($originalNameOfFile);

        $file = new SenderFile();
        $file->original_name = $path_parts['filename'] . '.' . strtolower($path_parts['extension']);
        $file->size = $request->file('log_file')->getSize();
        $file->uin = $request->input('uin');
        $file->folder = $folder_id;
        $file->server_name = '';
        $file->save();


        try {

            $path = Storage::putFile(
                'log_file_storage' . DIRECTORY_SEPARATOR . 'SENDER_FILES' . DIRECTORY_SEPARATOR .
                ServiceController::createFolderForFileByNumber($file->id),
                $request->file('log_file')
            );

        } catch (QueryException $e) {
            $file->delete();
            return Feedback::getFeedback(607);
        }


        if ($path === false) {
            $file->delete();
            return Feedback::getFeedback(606);
        }

        $file->server_name = $path;
        $file->save();

        return Feedback::getFeedback(0, [
            'id' => $file->id,
            'uin' => $file->uin,
        ]);
    }

    public function get(Request $request)
    {

        $folder_id = null;
        if ($request->has('folder_id')) {

            if (!SenderFolder::where('id', '=', $request->input('folder_id'))->exists()) {
                return Feedback::getFeedback(604);
            } else {
                $folder_id = $request->input('folder_id');
            }
        }

        $items = DB::table('sender_files')
            ->where('folder', $folder_id)
            ->select(['id', 'original_name as filename', 'created_at as date'])
            ->orderBy('filename', 'asc')
            ->orderBy('date', 'asc')
            ->get();

        // Подменяем значения полей
        $items->transform(function ($item, $key) {

            // Имя файла и расширение
            $path_parts = pathinfo($item->filename);
            $item->filename = $path_parts['filename'];
            $item->extension = $path_parts['extension'];

            // Статус, собственник, ошибки
            $check = Check::where('filename', $item->filename)->latest()->first();
            if (!is_null($check)) {
                $item->status = $check->status;
                $item->owner = ApiAuthController::getSurnameAndNameOfUserById($check->owner);
                $item->mistake_count = $check->mistake_count;
            }

            return $item;
        });

        return Feedback::getFeedback(0, [
            'items' => $items->toArray()
        ]);

    }

    public static function delete(Request $request)
    {
        $file_id = intval($request->input('id', 0));
        $file = SenderFile::find($file_id);

        if (!is_null($file)) {

            try {
                Storage::delete($file->server_name);

            } catch (QueryException $e) {

                return Feedback::getFeedback(603);
            }
        }

        SenderFile::destroy($file_id);

        return Feedback::getFeedback(0);
    }

    public function download(Request $request)
    {
        $file_id = null;
        if ($request->has('id')) {

            if (!SenderFile::where('id', '=', $request->input('id'))->exists()) {
                return Feedback::getFeedback(604);
            } else {
                $file_id = $request->input('id');
            }
        }

        $file = SenderFile::find($file_id);

        $headers = array(
            'Content-Type' => 'application/octet-stream',
            'Access-Control-Expose-Headers' => 'Content-Filename',
            'Content-Filename' => rawurlencode($file->original_name)
        );

        return response()->download(storage_path("app/" . $file->server_name), "", $headers);
    }

    public function downloadAll(Request $request)
    {
        $file_ids = $request->input('ids', []);

        $filesForZipArchive = [];
        foreach ($file_ids as $file_id) {

            $file = SenderFile::find($file_id);

            if (!is_null($file)) {

                $filesForZipArchive[] = [
                    'absolute_path' => storage_path("app/" . $file->server_name),
                    'filename' => $file->original_name
                ];
            }
        }

        return ZipArchiveController::download($filesForZipArchive);
    }


}
