<?php

namespace App\Http\Middleware;

use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\SettingsController;
use App\Models\Log;
use App\Models\Title;
use Closure;
use App\Http\Controllers\FeedbackController as Feedback;

class ApiDeleteFirstRecordForTransmittal
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
        $record = Log::find($request->input('id', null));

        if (is_null($record)) {
            return Feedback::getFeedback(305);
        }

//        $title_id = $request->input('title');
        $title_name = Title::find($record->title)->name;

        // Проверяем является ли записей первой
        $reg_exp = SettingsController::take('TRANSMITTAL_REG_EXP');

        if (preg_match($reg_exp, $title_name) && Log::where('title', $record->title)->first() == $record) {

            $user = ApiAuthController::getUserByToken($request->input('access_token'));
            if (!$user->mayDo('DELETE_FIRST_RECORD_FOR_TRANSMITTAL')) {
                return Feedback::getFeedback(309);
            }
        }

        return $next($request);
    }
}
