<?php

namespace App\Http\Controllers;

use App\Models\Doc;
use App\Models\Log;
use App\Models\Title;
use App\Models\UploadedFile;
use Illuminate\Http\Request;
use App\Http\Controllers\FeedbackController as Feedback;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use DateTime;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log as MyLog;


class DocsController extends Controller
{

    public function search(Request $request)
    {
        $parameters = [
            'code_1' => '',
            'code_2' => '',
            'class' => '',
            'revision' => '',
            'title_en' => '',
            'title_ru' => ''
        ];

        foreach ($parameters as $key => $value) {
            $parameters[$key] = $request->input($key, '');
        }

        $transmittalName = $request->input('transmittal', '');
        $date1 = intval(trim($request->input('date1', '')));
        $date2 = intval(trim($request->input('date2', '')));
        $isOnlyLast = $request->input('is_only_last', false);

        //DATE
        $dayStartDate = 1;
        $dayEndDate = 9999999999;

        if ($date1 != '' && $date2 != '') {
            $dayStartDate = intval(DateTime::createFromFormat('U', min($date1, $date2))->setTime(0, 0, 0)->format('U'));
            $dayEndDate = intval(DateTime::createFromFormat('U', max($date1, $date2))->setTime(23, 59, 59)->format('U'));
        }


        $firstLogs = DB::table('logs')
            ->select(DB::raw('MIN(id) as id, title'))
            ->groupBy('title');

        $maxRevs = DB::table('docs')
            ->select(DB::raw('MAX(revision_priority),  id'))
            ->groupBy('code_1');

        $query = DB::table('docs');

        if ($isOnlyLast) {
            $query->joinSub($maxRevs, 'maxRevs', function ($join) {
                $join->on('docs.id', '=', 'maxRevs.id');
            });
        }

        $query->joinSub($firstLogs, 'firstLogs', function ($join) {
            $join->on('docs.transmittal', '=', 'firstLogs.title');
        });



        $query->select(
            'docs.id',
            'docs.code_1',
            'docs.code_2',
            'docs.revision',
            'docs.revision_priority',
            'docs.class',
            'docs.title_en',
            'docs.title_ru',
            'titles.id as transmittal_id',
            'titles.name as transmittal',
            'titles.created_at as date',
            'firstLogs.id as log_id'
        );

        $query->join('titles', function ($join) {
            $join->on('docs.transmittal', '=', 'titles.id');
        });


        // VUE
        // item.id, item.date, item.transmittal, item.code_1, item.code_2,
        // item.revision, item.class, item.title_en, item.title_ru
        // item.primaryPdfFileId
        // item.files -> file.id, file.name

        //SELECT docs.id, docs.code_1, docs.code_2, docs.revision, docs.class, docs.title_en, docs.title_ru, titles.name FROM docs
        //LEFT JOIN titles ON titles.id = docs.transmittal WHERE docs.code_2 like "%66340-КМ1%"  ORDER BY code_2

        //SELECT docs.id, docs.code_1, docs.code_2, docs.revision, MAX(docs.revision_priority), docs.class, docs.title_en, docs.title_ru, titles.name FROM docs
        //LEFT JOIN titles ON titles.id = docs.transmittal  GROUP BY code_1 ORDER BY code_2

        foreach ($parameters as $key => $value) {
            if ($value != '') {
                $query->where('docs.' . $key, 'like', '%' . $value . '%');
            }
        }

        if ($transmittalName != '') {
            $query->where('titles.name', 'like', '%' . $transmittalName . '%');
        }

        $query->whereBetween('date', [$dayStartDate, $dayEndDate]);

        DB::connection()->enableQueryLog();

        $query->orderBy('code_1', 'asc');
        $query->orderBy('revision_priority', 'asc');

        $docs = $query->get();

        // Ищем файлы

        $regexForPdfFile = SettingsController::take('DOCS_REG_EXP_FOR_PDF_FILE');

        MyLog::debug(DB::getQueryLog());
        MyLog::debug('Count of docs = ' . count($docs));

        foreach ($docs as $doc) {

            // обрезаем TCM номер
            $cleanedCode_1 = $doc->code_1;
            $pos = strpos($cleanedCode_1, '_');

            if ($pos) {
                $cleanedCode_1 = substr($cleanedCode_1, 0, $pos);
            }

            $files = UploadedFile::where('log', $doc->log_id)
                ->where('original_name', 'like', '%' . $cleanedCode_1 . '%')
                ->orderBy('original_name', 'asc')
                ->get();

            MyLog::debug('Count of files for log_id('.$doc->log_id.') and original_name('.$cleanedCode_1.') = ' . count($files));

            $doc->files = [];
            $doc->primaryPdfFileId = null;

            foreach ($files as $file) {
                if (
                    is_null($doc->primaryPdfFileId) &&
                    preg_match($regexForPdfFile, $file->original_name)
                ) {
                    $doc->primaryPdfFileId = $file->id;
                }
                $doc->files[] = ['name' => $file->original_name, 'id' => $file->id];
            }

        }



        return Feedback::getFeedback(0, [
            'items' => $docs->toArray(),
        ]);

    }

    public function getListOfTransmittal(Request $request)
    {

        $transmittal_name = trim($request->input('transmittal', ''));
        $transmittal = Title::where('name', $transmittal_name)->first();

        if (is_null($transmittal)) {
            return Feedback::getFeedback(402);
        }

        $items = Doc::where('transmittal', $transmittal->id)->get();

        return Feedback::getFeedback(0, [
            'items' => $items->toArray()
        ]);

    }

    public function addNewDocumentToTransmittal(Request $request)
    {

        $transmittal_name = trim($request->input('transmittal', ''));
        $transmittal = Title::where('name', $transmittal_name)->first();

        if (is_null($transmittal)) {
            return Feedback::getFeedback(402);
        }

        try {

            $doc = new Doc;

            $doc->code_1 = '???';
            $doc->revision = '???';
            $doc->transmittal = $transmittal->id;

            $doc->save();

        } catch (Exception $e) {

            return Feedback::getFeedback(1012, [
                'exception' => $e

            ]);

        }

        return Feedback::getFeedback(0);

    }

    public function saveListOfTransmittal(Request $request)
    {

        if (!$request->has('items')) {
            return Feedback::getFeedback(1001);
        }

        foreach ($request->input('items') as $item) {

            if (!array_key_exists('id', $item)) {
                return Feedback::getFeedback(1008);
            }

            if (!array_key_exists('code_1', $item)) {
                return Feedback::getFeedback(1002);
            }

            if ($item['code_1'] == '') {
                return Feedback::getFeedback(1009);
            }

            if (!array_key_exists('code_2', $item)) {
                return Feedback::getFeedback(1003);
            }

            if (!array_key_exists('revision', $item)) {
                return Feedback::getFeedback(1004);
            }

            if (!$this->isRevisionCorrect($item['revision'])) {
                return Feedback::getFeedback(1010);
            }

            if (!array_key_exists('class', $item)) {
                return Feedback::getFeedback(1005);
            }

            if (!array_key_exists('title_ru', $item)) {
                return Feedback::getFeedback(1006);
            }

            if (!array_key_exists('title_en', $item)) {
                return Feedback::getFeedback(1007);
            }

            $doc = Doc::find($item['id']);

            if (is_null($doc)) {
                return Feedback::getFeedback(1011);
            }

            $doc->code_1 = $item['code_1'];
            $doc->code_2 = $item['code_2'];
            $doc->revision = $item['revision'];

            $doc->class = $item['class'];
            $doc->title_en = $item['title_en'];
            $doc->title_ru = $item['title_ru'];

            $priority_index = $this->getRevisionPriorityIndex($item['revision']);
            if ($priority_index !== false) {
                $doc->revision_priority = $priority_index;
            }

            $doc->save();

        }

        return Feedback::getFeedback(0);

    }

    private function listOfCorrectRevisions()
    {
        $s = SettingsController::take('DOCS_REV_LIST');

        try {
            $list = explode('|', $s);
        } catch (\Exception $e) {
            $list = [];
        }

        return $list;
    }

    private function isRevisionCorrect($rev)
    {
        return in_array($rev, $this->listOfCorrectRevisions(), true);
    }

    private function getRevisionPriorityIndex($rev)
    {
        return array_search($rev, $this->listOfCorrectRevisions(), true);
    }

    public function deleteDocumentFromTransmittal(Request $request)
    {

        if (!$request->has('doc_id')) {
            return Feedback::getFeedback(1008);
        }

        return (Doc::destroy($request->input('doc_id'))) ? Feedback::getFeedback(0) : Feedback::getFeedback(1013);

    }

    public function upload(Request $request)
    {

        if (!$request->hasFile('log_file')) {
            return Feedback::getFeedback(601);
        };

        if (!$request->file('log_file')->isValid()) {
            return Feedback::getFeedback(602);
        }

        $originalNameOfFile = $request->file('log_file')->getClientOriginalName();

        if (!$this->validateNameOfNewFile($originalNameOfFile)) {
            return Feedback::getFeedback(609);
        }

        try {

            $path = Storage::putFile(
                'log_file_storage' . DIRECTORY_SEPARATOR . 'TEMPORARY_FILES',
                $request->file('log_file')
            );

        } catch (QueryException $e) {
            return Feedback::getFeedback(607);
        }


        if ($path === false) {
            return Feedback::getFeedback(606);
        }

        // Читаем JSON файл

        $list = json_decode($this->removeUtf8ByteOrderMark(file_get_contents(storage_path("app/" . $path))), true);

        Storage::delete($path);

        if (is_null($list)) {
            return Feedback::getFeedback(1014);
        }

        try {

            $transmittal_name = $list['TRANSMITTAL'];
            $transmittal = Title::where('name', $transmittal_name)->first();

            if (is_null($transmittal)) {
                $transmittal = new Title;
                $transmittal->name = $list['TRANSMITTAL'];
                $transmittal->status = SettingsController::take('STATUS_ID_FOR_NEW_TRANSMITTAL');
            }

            $transmittal->description = $list['SUMMARY'];
            $transmittal->volume = $list['COUNT'];
            $transmittal->predecessor = $list['PURPOSE'];

            $transmittal->save();

            $log = Log::where('title', $transmittal->id)->first();

            if (is_null($log)) {
                $log = new Log();
                $log->title = $transmittal->id;
                $log->from = UserController::getUserId($request);
                $log->to = UserController::getUserId($request);
                $log->owner = UserController::getUserId($request);
            }


            $text = '<p>Трансмиттал : ' . $list['TRANSMITTAL'] . '</p>';
            $text .= '<p>Описание : ' . $list['SUMMARY'] . '</p>';
            $text .= '<p>Назначение : ' . $list['PURPOSE'] . '</p>';
            $text .= '<p>Нужен ли ответ : ' . $list['IS_REPLY_REQUESTED'] . '</p>';
            $text .= '<p>К дате ' . $list['REPLY_BY'] . '</p>';
            $text .= '<p>Кол-во документов: ' . $list['COUNT'] . '</p>';
            $text .= '<p>Дата : ' . $list['DATE'];

            $log->what = $text;
            $log->created_at = Carbon::createFromFormat('d.m.Y', $list['DATE'])->timestamp;
            $log->save();

            // Очищаем содержимое трансмиттала перед загрузкой
            Doc::where('transmittal', $transmittal->id)->delete();

            foreach ($list['DOCS'] as $item) {

                $doc = new Doc;

                $doc->code_1 = $item['CODE_1'];
                $doc->code_2 = $item['CODE_2'];
                $doc->revision = $item['REVISION'];
                $doc->class = $item['CLASS'];
                $doc->transmittal = $transmittal->id;
                $doc->title_en = $item['TITLE_EN'];
                $doc->title_ru = $item['TITLE_RU'];

                $doc->save();
            }

            unset($list['DOCS']);


        } catch (Exception $e) {
            return Feedback::getFeedback(1014);
        }


        return Feedback::getFeedback(0);
    }

    private function validateNameOfNewFile($fileNameWithExtension)
    {
        $regExpForNewFile = SettingsController::take('DOCS_REG_EXP_FOR_LIST_FILE');
        return (preg_match($regExpForNewFile, $fileNameWithExtension) === 1);
    }

    private function removeUtf8ByteOrderMark($text)
    {
        $bom = pack('H*', 'EFBBBF');
        $text = preg_replace("/^$bom/", '', $text);
        return $text;
    }

    public function updatePriorityIndexes(Request $request)
    {
        for ($i = 0; $i < count($this->listOfCorrectRevisions()); $i++) {
            Doc::where('revision', $this->listOfCorrectRevisions()[$i])->update(['revision_priority' => $i]);
        }

        return Feedback::getFeedback(0);
    }

}
