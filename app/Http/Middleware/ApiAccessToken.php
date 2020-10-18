<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\ApiAuthController;

class ApiAccessToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = ApiAuthController::isTokenValid($request);
        $json = json_decode($response, true);

        return ($json['success'] == 1) ? $next($request) : $response;

    }
}
