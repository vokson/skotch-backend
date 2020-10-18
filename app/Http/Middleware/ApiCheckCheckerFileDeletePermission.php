<?php

namespace App\Http\Middleware;

use App\Http\Controllers\ApiAuthController;
use Closure;
use App\Http\Controllers\FeedbackController As Feedback;
use App\Models\Check;

class ApiCheckCheckerFileDeletePermission
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
        // Ограничиваем удаление файлов Checker для не собственников файлов, а также если запись не последняя

        $user = ApiAuthController::getUserByToken($request->input('access_token'));

        if (!$request->has('id')) {
            return Feedback::getFeedback(701);
        }

        if (!Check::where('id', '=', $request->input('id'))->exists()) {
            return Feedback::getFeedback(701);
        }

        $check = Check::find($request->input('id'));
        $lastRecord = Check::where('filename', $check->filename)->latest()->first();

        // Если запись не последняя
        if ($check->id != $lastRecord->id) {
            return Feedback::getFeedback(104);
        }

        // Если user не является собсственником документа
        if ($user->mayDo('DELETE_NON_OWNED_CHECK_FILE') || $user->id == $check->owner) {
           return $next($request);
        }

        return Feedback::getFeedback(104);

    }
}
