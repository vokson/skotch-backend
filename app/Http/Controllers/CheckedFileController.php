<?php

namespace App\Http\Controllers;

use App\Models\ApiUser;
use App\Models\Check;
use App\Models\CheckedFile;
use Illuminate\Http\Request;
use App\Http\Controllers\FeedbackController As Feedback;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;

class CheckedFileController extends Controller
{
    public function upload(Request $request)
    {

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

        if (!CheckController::validateNameOfNewFile($originalNameOfFile) &&
            !CheckController::validateNameOfCheckedFile($originalNameOfFile)) {
            return Feedback::getFeedback(609);
        }

        $path_parts = pathinfo($originalNameOfFile);

        $file = new CheckedFile();
        $file->original_name = $path_parts['filename'] . '.' . strtolower($path_parts['extension']);
        $file->size = $request->file('log_file')->getSize();
        $file->uin = $request->input('uin');
        $file->server_name = '';
        $file->save();


        try {

            $path = Storage::putFile(
                'log_file_storage' . DIRECTORY_SEPARATOR . 'CHECKED_FILES' . DIRECTORY_SEPARATOR .
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

        $token = $request->input('access_token');
        $user = ApiUser::where('access_token', $token)->first();

        $errorCode =  CheckController::add($file->id, $user->id);

        if ($errorCode == 0) {
            return Feedback::getFeedback(0, [
                'id' => $file->id,
                'uin' => $file->uin,
            ]);
        } else {
            return Feedback::getFeedback($errorCode);
        }
    }

    public static function deleteById($id)
    {
        $file = CheckedFile::find($id);

        if (!is_null($file)) {

            try {
                Storage::delete($file->server_name);

            } catch (QueryException $e) {

                return false;
            }
        }

        CheckedFile::destroy($id);

        return true;
    }

    public function download(Request $request)
    {
        $file_id = null;
        if ($request->has('id')) {

            if (!CheckedFile::where('id', '=', $request->get('id'))->exists()) {
                return Feedback::getFeedback(604);
            } else {
                $file_id = $request->input('id');
            }
        }

        $file = CheckedFile::find($file_id);
        $filename = $file->original_name;

        try {
            $check = Check::where('file_id', $file->id)->first();
            if ($check->status == -1) {
                $owner = ApiUser::find($check->owner);
                $email = $owner->email;
                $email = explode('@', $email);
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                $name = pathinfo($filename, PATHINFO_FILENAME);
                $filename = $name . '_' . $email[0];
                if ($ext != '') {
                    $filename .= '.' . $ext;
                }
            }

        } catch (\Exception $e) {
            $filename = $file->original_name;
        }

        $headers = array(
            'Content-Type' => 'application/octet-stream',
            'Access-Control-Expose-Headers' => 'Content-Filename',
            'Content-Filename' => rawurlencode($filename)
        );

        return response()->download(storage_path("app/" . $file->server_name), "", $headers);
    }

    public function downloadAll(Request $request)
    {
        $file_ids = $request->get('ids', []);

        $filesForZipArchive = [];
        foreach ($file_ids as $file_id) {

            $file = CheckedFile::find($file_id);

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
