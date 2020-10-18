<?php

namespace App\Http\Middleware;

use App\Http\Controllers\ApiAuthController;
use App\Models\UploadedFile;
use Closure;
use App\Http\Controllers\FeedbackController As Feedback;
use App\Models\Log;
use App\Models\Title;

class ApiCheckLogFileEditRegExpPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = ApiAuthController::getUserByToken($request->input('access_token'));

        if ($request->has('log_id')) { // если загрузка файла
            $log = Log::find($request->input('log_id'));

        } else { // если удаление файла
            $file = UploadedFile::find($request->input('id'));
            $log = Log::find($file->log);
        }

        $title = Title::find($log->title);
        $result = preg_match($user->permission_expression, $title->name);

        if ($result != 1) {
            return Feedback::getFeedback(106);
        }


        return $next($request);
    }
}
