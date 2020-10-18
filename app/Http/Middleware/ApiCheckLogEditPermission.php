<?php

namespace App\Http\Middleware;

use App\Http\Controllers\ApiAuthController;
use Closure;
use App\Http\Controllers\FeedbackController As Feedback;
use App\Models\Log;

class ApiCheckLogEditPermission
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
        $id = $request->input('id', null);
        if (is_null($id)) {
            return $next($request);
        }


        $user = ApiAuthController::getUserByToken($request->input('access_token'));
        $log = Log::find($id);

        if (is_null($log)) {
            return Feedback::getFeedback(104);
        }

        if (!$user->mayDo('EDIT_NON_OWNED_LOG_RECORD') && ($log->owner != $user->id)) {
            return Feedback::getFeedback(104);
        }

        return $next($request);
    }
}
