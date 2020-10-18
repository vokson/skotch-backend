<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Title;
use Illuminate\Support\Facades\Storage;
use App\Models\Log;
use App\Http\Controllers\FeedbackController as Feedback;
use Illuminate\Database\QueryException;

class TaskController extends Controller
{
    public function create(Request $request)
    {
        if (!Title::where('id', '=', $request->input('title'))->exists()) {
            return Feedback::getFeedback(303);
        }

        $task_id = SettingsController::take('LAST_TASK_NUMBER') + 1;

        if (Storage::exists($this->getFolderPathById($task_id))) {
            return Feedback::getFeedback(612);
        }

        if (!$this->createFolder($task_id)) {
            return Feedback::getFeedback(611);
        }

        SettingsController::save('LAST_TASK_NUMBER', $task_id);

        $owner_id = UserController::getUserId($request);

        $log = new Log;

        $log->owner = $owner_id;
        $log->is_new = false;
        $log->to = $owner_id;
        $log->from = $owner_id;
        $log->title = $request->input('title');
        $log->what = SettingsController::take('CREATE_TASK_NOTIFICATION') . ' ' .  $task_id;

        $log->save();

        return Feedback::getFeedback(0);
    }

    private function getFolderPathById($id)
    {
        return 'tasks' . DIRECTORY_SEPARATOR .
            ServiceController::createFolderForFileByNumber($id, 100) . DIRECTORY_SEPARATOR .
            'TASK ' . sprintf("%04d", $id);
    }

    private function createFolder($id)
    {
        try {

            $result = Storage::makeDirectory($this->getFolderPathById($id));

        } catch (QueryException $e) {

            return false;
        }

       return $result;

    }
}
