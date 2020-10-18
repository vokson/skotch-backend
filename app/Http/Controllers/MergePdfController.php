<?php

namespace App\Http\Controllers;

use App\Models\PdfMergeFile;
use Illuminate\Http\Request;
use App\Http\Controllers\FeedbackController as Feedback;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\SettingsController as Settings;
use Illuminate\Database\QueryException;

class MergePdfController extends Controller
{

    public function get(Request $request)
    {

        $items = DB::table('pdf_merge_files')
            ->select(['id', 'drop_id as group', 'owner', 'original_name as filename', 'created_at as date', 'is_name'])
            ->where('owner', ApiAuthController::id($request))
            ->orderBy('group', 'asc')
            ->orderBy('filename', 'asc')
            ->get();

        return Feedback::getFeedback(0, [
            'items' => $items->toArray(),
        ]);
    }

    public static function setMainName(Request $request)
    {
        PdfMergeFile::where('owner', ApiAuthController::id($request))->update(['is_name' => false]);

        $file_id = intval($request->input('id', 0));

        try {

            PdfMergeFile::where('owner', ApiAuthController::id($request))
                ->where('id', $file_id)
                ->update(['is_name' => true]);

        } catch (QueryException $e) {

            return Feedback::getFeedback(604);
        }

        return Feedback::getFeedback(0);
    }

    public function clean(Request $request)
    {

        $filesToBeDeleted = PdfMergeFile::where('owner', ApiAuthController::id($request))->get();

        foreach ($filesToBeDeleted as $file) {

            try {
                Storage::delete($file->server_name);

            } catch (QueryException $e) {

                return Feedback::getFeedback(603);
            }

            PdfMergeFile::destroy($file->id);

        }

        return Feedback::getFeedback(0);
    }


    public function upload(Request $request)
    {

        if (!$request->hasFile('pdf_file')) {
            return Feedback::getFeedback(601);
        };

        if (!$request->file('pdf_file')->isValid()) {
            return Feedback::getFeedback(602);
        }

        if (!$request->has('uin')) {
            return Feedback::getFeedback(605);
        }

        if (!$request->has('drop_uin')) {
            return Feedback::getFeedback(605);
        }

        $originalNameOfFile = $request->file('pdf_file')->getClientOriginalName();

        if (!$this->validateNameOfNewFile($originalNameOfFile)) {
            return Feedback::getFeedback(609);
        }

        $owner = ApiAuthController::id($request);
        $path_parts = pathinfo($originalNameOfFile);

        $drop_uin = $request->input('drop_uin');

        DB::beginTransaction(); // НАЧАЛО ТРАНЗАКЦИИ

        $maxDropIdForCurrentUin = PdfMergeFile::where('drop_uin', $drop_uin)->max('drop_id');
        $maxDropIdForCurrentUser = PdfMergeFile::where('owner', $owner)->max('drop_id');

        DB::commit(); // КОНЕЦ ТРАНЗАКЦИИ

        $drop_id = (is_null($maxDropIdForCurrentUin)) ? ($maxDropIdForCurrentUser + 1) : $maxDropIdForCurrentUin;

        $file = new PdfMergeFile();
        $file->original_name = $path_parts['filename'] . '.' . strtolower($path_parts['extension']);
        $file->size = $request->file('pdf_file')->getSize();
        $file->uin = $request->input('uin');
        $file->drop_id = $drop_id;
        $file->drop_uin = $drop_uin;
        $file->server_name = '';
        $file->owner = $owner;
        $file->save();

        // Попытка сохранить файл

        try {

            $path = Storage::putFile(
                config('filesystems.pdf_merge_files_path'),
//                'log_file_storage' . DIRECTORY_SEPARATOR . 'PDF_MERGE_FILES',
                $request->file('pdf_file')
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

        // Проверка на главное имя
        if (is_null($this->getMainNameOfMergedPdfForUser($file->owner))) {
            $file->is_name = true;
            $file->save();
        }


        return Feedback::getFeedback(0, [
            'id' => $file->id,
            'uin' => $file->uin,
        ]);
    }

    private function getMainNameOfMergedPdfForUser($user_id)
    {
        $result = PdfMergeFile::where('owner', $user_id)->where('is_name', 1)->first();
        return (is_null($result)) ? null : $result->original_name;
    }

    private function validateNameOfNewFile($fileNameWithExtension)
    {
        $regExpForNewFile = "/.*\.(pdf|PDF){1}$/";
        return (preg_match($regExpForNewFile, $fileNameWithExtension) === 1);
    }


    public function download(Request $request)
    {
        $this->cleanOldMergedFiles();

        $pathOfMergedFile = config('filesystems.pdf_merge_files_path') . DIRECTORY_SEPARATOR . 'MERGED_' . uniqid() . '.pdf';

        $files = DB::table('pdf_merge_files')
            ->select(['id', 'drop_id', 'owner', 'server_name', 'original_name'])
            ->where('owner', ApiAuthController::id($request))
            ->orderBy('drop_id', 'asc')
            ->orderBy('original_name', 'asc')
            ->get();

        $command_string = 'cd ' . storage_path("app") . '; pdftk ';

        foreach ($files as $file) {
            $command_string .= $file->server_name . ' ';
        }

        $command_string .= 'cat output ' . $pathOfMergedFile . ' 2>&1';

        $output = [];
        $result = null;
        exec($command_string, $output, $result);

        //LOG
//        file_put_contents(
//            storage_path("app") . DIRECTORY_SEPARATOR .
//            str_replace('.pdf', '.txt', $pathOfMergedFile),
//            print_r(
//            [
//                'command' => $command_string,
//                'result' => $result,
//                'output' => $output,
//                'path' => storage_path('app') . DIRECTORY_SEPARATOR . $pathOfMergedFile,
//                'is_exist' => file_exists(storage_path('app') . DIRECTORY_SEPARATOR . $pathOfMergedFile)
//
//            ], true)
//        );

        if (!file_exists(storage_path('app') . DIRECTORY_SEPARATOR . $pathOfMergedFile)) {
            return Feedback::getFeedback(610, [
                'command' => $command_string,
                'result' => $result,
                'output' => print_r($output, true),
                'path' => storage_path('app') . DIRECTORY_SEPARATOR . $pathOfMergedFile
            ]);
        }

        $headers = array(
            'Content-Type' => 'application/octet-stream',
            'Access-Control-Expose-Headers' => 'Content-Filename',
            'Content-Filename' => urlencode($this->getMainNameOfMergedPdfForUser(ApiAuthController::id($request)))
//            'Content-Filename' => storage_path('app') . DIRECTORY_SEPARATOR . $pathOfMergedFile
        );

        return response()->download(storage_path('app') . DIRECTORY_SEPARATOR . $pathOfMergedFile, "", $headers);
    }

    private function cleanOldMergedFiles()
    {
        foreach (glob(
                     storage_path('app') .
                     DIRECTORY_SEPARATOR .
                     config('filesystems.pdf_merge_files_path') .
                     DIRECTORY_SEPARATOR .
                     'MERGED_*'
                 ) as $fileName) {
            if ((microtime(true) - filectime($fileName) > Settings::take('ARCHIVE_STORAGE_TIME'))) {
                unlink($fileName);
            }
        }
    }
}
