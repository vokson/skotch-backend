<?php

namespace App\Http\Middleware;

use App\Http\Controllers\ApiAuthController;
use Closure;
use App\Http\Controllers\FeedbackController As Feedback;
use App\Models\Log;
use App\Models\Title;

class ApiCheckLogEditRegExpPermission
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


        try {
            $title =  Title::find($request->input('title'));

            if (is_null($title)) {

                $log = Log::find($request->input('id'));
                if (is_null($log)) {
                    return Feedback::getFeedback(106);
                }

                $title = Title::find($log->title);
            }

        } catch (\Exception $e) {
            return Feedback::getFeedback(106);
        }

        if (preg_match($user->permission_expression, $title->name) != 1) {
            return Feedback::getFeedback(106);
        }

        return $next($request);
    }
}
