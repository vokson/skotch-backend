<?php

namespace App\Http\Controllers;

use App\Models\Title;
use App\Models\TitleHistoryRecord;
use App\Models\Status;
use App\Http\Controllers\FeedbackController as Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TitleHistoryController extends Controller
{
    public static function record($user_id, $title_id)
    {
        $title = Title::find($title_id);
        $status = Status::find($title->status);
        $record = new TitleHistoryRecord;

        $record->title_id = $title_id;
        $record->user_id = $user_id;
        $record->name = $title->name;
        $record->status = $status->name;
        $record->predecessor = $title->predecessor;
        $record->description = $title->description;
        $record->volume = $title->volume;

        $record->save();
    }

    public static function titleDeletedRecord($user_id, $title_id)
    {
        $record = new TitleHistoryRecord;
        $record->title_id = $title_id;
        $record->user_id = $user_id;
        $record->status = "DELETED";
        $record->save();
    }

    public function get(Request $request)
    {

        $id = null;
        if (!$request->has('id')) {
            return Feedback::getFeedback(401);
        } else {
            $id = intval(trim($request->input('id')));
        }

        $items = DB::table('titles_history')
            ->where('title_id', '=', $id)
            ->select(['id', 'name', 'status', 'predecessor', 'description', 'volume', 'created_at as date'])
            ->get();


        return Feedback::getFeedback(0, [
            'items' => $items->toArray(),
        ]);


    }
}
