<?php

namespace App\Http\Controllers;

use App\Models\Check;
use App\Models\CheckedFile;
use Illuminate\Http\Request;
use App\Http\Controllers\FeedbackController as Feedback;
use Illuminate\Support\Facades\DB;
use DateTime;

class CheckController extends Controller
{

    public function get(Request $request)
    {
        $status_yes = $request->input('status_yes', true);
        $status_no = $request->input('status_no', true);
        $status_question = $request->input('status_question', true);

        $statuses = [];
        if ($status_yes) $statuses[] = 1;
        if ($status_question) $statuses[] = 0;
        if ($status_no) $statuses[] = -1;


        $owner = trim($request->input('owner', ''));
        $filename = trim($request->input('filename', ''));
        $extension = trim($request->input('extension', ''));
        $mistake_count = trim($request->input('mistake_count', ''));
        $isOnlyLast = $request->input('is_only_last', false);

        $date1 = intval(trim($request->input('date1', '')));
        $date2 = intval(trim($request->input('date2', '')));

        //DATE
        $dayStartDate = 1;
        $dayEndDate = 9999999999;

        if ($date1 != '' && $date2 != '') {
            $dayStartDate = DateTime::createFromFormat('U', min($date1, $date2))->setTime(0, 0, 0)->format('U');
            $dayEndDate = DateTime::createFromFormat('U', max($date1, $date2))->setTime(23, 59, 59)->format('U');
        }

        [$idUsers, $idNamesUsers] = $this->getNamesUsers($owner);

        $query = DB::table('checks')
            ->whereBetween('updated_at', [$dayStartDate, $dayEndDate])
            ->where('filename', 'like', '%' . $filename . '%')
            ->where('extension', 'like', '%' . $extension . '%')
            ->where('mistake_count', 'like', '%' . $mistake_count . '%')
            ->whereIn('owner', $idUsers);

        if ($isOnlyLast == true) {
            $query
                ->select(DB::raw('"id", "file_id", "filename", "extension", "status", "mistake_count", "owner", max("updated_at") as "date"'))
                ->groupBy('filename');

        } else {
            $query->select(['id', 'file_id', "filename", "extension", 'status', 'mistake_count', 'owner', 'updated_at as date']);
        }


        $items = $query
            ->orderBy('filename', 'asc')
            ->orderBy('date', 'asc')
            ->get();

        // Подменяем id на значения полей из других таблиц
        $items->transform(function ($item, $key) use ($idNamesUsers) {
            $item->owner = $idNamesUsers[$item->owner];
            return $item;
        });


        // Statuses отдельно, чтобы иметь возможность отоборать статусы после выбора последних записей
        $items = $items->whereIn('status', $statuses);

        return Feedback::getFeedback(0, [
            // array_values добавлено, потому что whereIn (также как и array_filter) выдает
            // ассоциативынй массив, что в данном случае не нужно
            'items' => array_values($items->toArray()),
        ]);

    }


    private function getNamesUsers($userIdPattern)
    {
        $users = DB::table('api_users')
            ->where('surname', 'like', '%' . $userIdPattern . '%')
            ->select('id', 'name', 'surname')
            ->get();

        $idUsers = $users->map(function ($item) {
            return $item->id;
        });

        $namesUsers = $users->map(function ($item) {
            return $item->surname . ' ' . $item->name;
        });

        return [$idUsers, array_combine($idUsers->toArray(), $namesUsers->toArray())];
    }

    public static function validateNameOfNewFile($fileNameWithExtension)
    {
        $regExpForNewFile = SettingsController::take('CHECKER_REG_EXP_FOR_NEW_FILE');
        return (preg_match($regExpForNewFile, $fileNameWithExtension) === 1);
    }

    public static function validateNameOfCheckedFile($fileNameWithExtension)
    {
        $regExpForCheckedFile = SettingsController::take('CHECKER_REG_EXP_FOR_CHECKED_FILE');
        return (preg_match($regExpForCheckedFile, $fileNameWithExtension) === 1);
    }

    public static function add($file_id, $owner_id)
    {
        $uploadedFile = CheckedFile::find($file_id);
        $path_parts = pathinfo($uploadedFile->original_name); // Filename without extension
        $path_parts['extension'] = strtolower($path_parts['extension']);

        if (self::validateNameOfNewFile($uploadedFile->original_name)) {
            return self::addRecordOfNewFile($path_parts['filename'], $path_parts['extension'], $file_id, $owner_id);
        } else {
            return self::addRecordOfCheckedFile($path_parts['filename'], $path_parts['extension'], $file_id, $owner_id);
        }
    }

    public static function addRecordOfNewFile($nameOfFileWithoutExtension, $extension, $file_id, $owner_id)
    {
        $record = Check::where('filename', $nameOfFileWithoutExtension)->latest()->first();

        if (!is_null($record) && $record->status == 0 && $record->owner == $owner_id) {
            // Пользователь хочет подменить файл
            if (!CheckedFileController::deleteById($record->file_id)) return 603;

            $record->file_id = $file_id;
            $record->save();
        } else {
            // Пользователь загружает файл в первый раз
            $record = new Check();
            $record->file_id = $file_id;
            $record->filename = $nameOfFileWithoutExtension;
            $record->extension = $extension;
            $record->status = 0;
            $record->mistake_count = 0;
            $record->owner = $owner_id;
            $record->save();
        }

        return 0;

    }

    public static function addRecordOfCheckedFile($nameOfFileWithoutExtension, $extension, $file_id, $owner_id)
    {
        //Разделяем имя файла
        $arr = explode('[', $nameOfFileWithoutExtension);

        $filename = $arr[0];
        $countOfMistakes = intval(substr($arr[1], 0, -1));
        $status = ($countOfMistakes === 0) ? 1 : -1;

        // Если согласовано положительно, то удаляем загруженный файл
        if ($status === 1) {
            if (!CheckedFileController::deleteById($file_id)) return 603;
        }

        $record = Check::where('filename', $filename)->latest()->first();

        if (!is_null($record) && $record->status == $status && $record->owner == $owner_id) {
            // Пользователь хочет подменить файл

            if ($status === -1) {
                if (!CheckedFileController::deleteById($record->file_id)) return 603;
                $record->file_id = $file_id;
                $record->mistake_count = $countOfMistakes;
                $record->save();
            }

        } else {
            // Пользователь загружает файл в первый раз
            $record = new Check();
            if ($status === -1) $record->file_id = $file_id;
            $record->filename = $filename;
            $record->extension = $extension;
            $record->status = $status;
            $record->mistake_count = $countOfMistakes;
            $record->owner = $owner_id;
            $record->save();
        }

        return 0;

    }

    public function delete(Request $request)
    {

        if (!$request->has('id')) {
            return Feedback::getFeedback(701);
        }

        if (!Check::where('id', '=', $request->input('id'))->exists()) {
            return Feedback::getFeedback(701);
        }

        $check = Check::find($request->input('id'));

        if (!is_null($check->file_id)) {
            if (!CheckedFileController::deleteById($check->file_id)) return 603;
        }

        $check->delete();

        return Feedback::getFeedback(0);
    }

    public static function count()
    {
        $collection = DB::table('checks')
            ->select('filename', 'status', DB::raw('MAX(updated_at)'))
            ->groupBy('filename')
            ->having('status', 0)
            ->get();

        return $collection->count();
    }
}
