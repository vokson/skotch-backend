<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\FeedbackController As Feedback;
use App\Models\ApiUser;
use App\Models\Log;

class ApiCheckLogMarkNewMessagePermission
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

        $token = $request->input('access_token');
        $user = ApiUser::where('access_token', $token)->first();

        $log = Log::find($request->input('id'));

        if (is_null($log) ) {
            return Feedback::getFeedback(104);
        }

        if ($log->to != $user->id) {
            return Feedback::getFeedback(104);
        }

        return $next($request);
    }
}
