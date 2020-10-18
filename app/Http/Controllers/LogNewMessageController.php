<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Log;
use App\Http\Controllers\FeedbackController as Feedback;
use App\Models\ApiUser;
use Illuminate\Support\Facades\DB;

class LogNewMessageController extends Controller
{
    public function set(Request $request)
    {

        $id = $request->input('id', null);

        if (!Log::where('id', '=', $id)->exists()) {
            return Feedback::getFeedback(305);
        }

        $log = Log::find($id);
        $log->is_new = !$log->is_new;
        $log->save();

        return Feedback::getFeedback(0);
    }

    public static function count(Request $request)
    {
        $token = $request->input('access_token');
        $user = ApiUser::where('access_token', $token)->first();

        $items = DB::table('logs')
            ->where('is_new', '=', true)
            ->where('to', '=', $user->id)
            ->get();

        return  $items->count();
    }
}
