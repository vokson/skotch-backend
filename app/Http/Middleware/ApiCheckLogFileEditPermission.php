<?php

namespace App\Http\Middleware;

use App\Http\Controllers\ApiAuthController;
use App\Models\UploadedFile;
use Closure;
use App\Http\Controllers\FeedbackController As Feedback;
use App\Models\Log;
use Illuminate\Support\Facades\Log as MyLog;

class ApiCheckLogFileEditPermission
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        MyLog::debug('ApiCheckLogFileEditPermission - START');
        $user = ApiAuthController::getUserByToken($request->input('access_token'));

        // Ограничиваем загрузку и удаление файлов Log для не собственников записей в случае
        // если роль не позволяет редактировать чужие файлы
        if ($user->mayDo('EDIT_NON_OWNED_LOG_RECORD_FILE')) {
            MyLog::debug('ApiCheckLogFileEditPermission - EDIT_NON_OWNED_LOG_RECORD_FILE = True');
            return $next($request);
        }

        MyLog::debug('ApiCheckLogFileEditPermission - EDIT_NON_OWNED_LOG_RECORD_FILE = False');

        if ($request->has('log_id')) { // если загрузка файла
            $log = Log::find($request->input('log_id'));

        } else { // если удаление файла
            $file = UploadedFile::find($request->input('id'));
            $log = Log::find($file->log);
        }

        MyLog::debug('ApiCheckLogFileEditPermission - log');
        MyLog::debug($log);

        if (is_null($log)) {
            return Feedback::getFeedback(104, [
                'uin' => $request->input('uin', '')
            ]);
        }

        if ($log->owner != $user->id) {
            return Feedback::getFeedback(104, [
                'uin' => $request->input('uin', '')
            ]);
        }

        MyLog::debug('ApiCheckLogFileEditPermission - FINISH');
        return $next($request);
    }
}
