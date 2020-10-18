<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Http\Controllers\FeedbackController As Feedback;
use Illuminate\Support\Facades\DB;
use App\Models\Title;

class TitleController extends Controller
{
    public function set(Request $request)
    {
        if (!$request->has('name')) {
            return Feedback::getFeedback(402);
        }

        if (!Status::where('id', '=', $request->input('status'))->exists()) {
            return Feedback::getFeedback(403);
        }

        if (!$request->has('predecessor')) {
            return Feedback::getFeedback(404);
        }

        if (!$request->has('description')) {
            return Feedback::getFeedback(404);
        }

        if (!$request->has('volume')) {
            return Feedback::getFeedback(404);
        }

        $id = null;
        if ($request->has('id')) {

            if (!Title::where('id', '=', $request->input('id'))->exists()) {
                return Feedback::getFeedback(401);
            } else {
                $id = $request->input('id');
            }
        }

        if (is_null($id)) {
            $title = new Title;
        } else {
            $title = Title::find($id);
        }

        $title->name = trim($request->input('name'));
        $title->status = $request->input('status');
        $title->predecessor = $request->input('predecessor');
        $title->description = $request->input('description');
        $title->volume = $request->input('volume');

        if ($title->name == "") {
            return Feedback::getFeedback(402);
        }

        try {
            $title->save();
        } catch (QueryException $e) {
            return Feedback::getFeedback(405);
        }

        TitleHistoryController::record(UserController::getUserId($request), $title->id);
        return Feedback::getFeedback(0);
    }

    public function get(Request $request)
    {
        $name = trim($request->input('name', ''));
        $status = trim($request->input('status', ''));
        $predecessor = trim($request->input('predecessor', ''));
        $description = trim($request->input('description', ''));
        $volume = trim($request->input('volume', ''));

        // STATUS

        $statuses = DB::table('statuses')
            ->where('name', 'like', '%' . $status . '%')
            ->select('id', 'name')
            ->get();

        $idStatuses = $statuses->map(function ($item) {
            return $item->id;
        });

        $namesStatuses = $statuses->map(function ($item) {
            return $item->name;
        });

        $idNamesStatuses = array_combine($idStatuses->toArray(), $namesStatuses->toArray());


        $items = DB::table('titles')
            ->where('name', 'like', '%' . $name . '%')
            ->where(function ($query) use ($predecessor) {

                $query->where('predecessor', 'like', '%' . $predecessor . '%');

                if ($predecessor == "") {
                    $query->orWhereNull('predecessor');
                }

            })
            ->where(function ($query) use ($description){

                $query->where('description', 'like', '%' . $description . '%');

                if ($description == "") {
                    $query->orWhereNull('description');
                }

            })
            ->where(function ($query) use ($volume) {

                $query->where('volume', 'like', '%' . $volume . '%');

                if ($volume == "") {
                    $query->orWhereNull('volume');
                }

            })

            ->whereIn('status', $idStatuses)
            ->select(['id', 'name', 'status', 'predecessor', 'description', 'volume'])
            ->orderBy('name', 'asc')
            ->get();


        // Подменяем id на значения полей из других таблиц

        $items->transform(function ($item, $key) use ($idNamesStatuses) {

            $item->status = $idNamesStatuses[$item->status];
            return $item;
        });


        return Feedback::getFeedback(0, [
            'items' => $items->toArray(),
        ]);


    }

    public function delete(Request $request)
    {

        if (!$request->has('id')) {
            return Feedback::getFeedback(401);
        }

        if (!Title::where('id', '=', $request->input('id'))->exists()) {
            return Feedback::getFeedback(401);
        }

        $title = Title::find($request->input('id'));
        $id = $title->id;


        try {
            $title->delete();
        } catch (QueryException $e) {
            return Feedback::getFeedback(206);
        }

        TitleHistoryController::titleDeletedRecord(UserController::getUserId($request), $id);
        return Feedback::getFeedback(0);
    }
}
