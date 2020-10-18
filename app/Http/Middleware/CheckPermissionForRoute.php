<?php

namespace App\Http\Middleware;

use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\FeedbackController as Feedback;
use Closure;

class CheckPermissionForRoute
{

    public function handle($request, Closure $next)
    {
        $uri = str_replace('api/', '', $request->path());
        $user = ApiAuthController::getUserByToken($request->input('access_token'));

        if (!$user->mayDo($uri)) {
            return Feedback::getFeedback(104);
        }

        return $next($request);
    }
}
