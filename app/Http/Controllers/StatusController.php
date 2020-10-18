<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Status;
use App\Http\Controllers\FeedbackController As Feedback;
use Illuminate\Database\QueryException;

class StatusController extends Controller
{
    public function add()
    {
        $item = new Status();
        $item->name = 'NONAME' . ' - ' . uniqid();
        $item->save();

        return Feedback::getFeedback(0);
    }

    public function delete(Request $request)
    {

        if (!$request->has('id')) {
            return Feedback::getFeedback(205);
        }

        if (!Status::where('id', '=', $request->input('id'))->exists()) {
            return Feedback::getFeedback(205);
        }

        $item = Status::find($request->input('id'));

        try {
            $item->delete();
        } catch (QueryException $e) {
            return Feedback::getFeedback(206);
        }

        return Feedback::getFeedback(0);
    }


    public function get(Request $request)
    {
        $parameters = [];

        foreach (Status::all() as $item) {
            $parameters[] = array_filter($item->toArray(), function ($k) {
                return ($k == 'id' || $k == 'name');
            }, ARRAY_FILTER_USE_KEY);
        }

        return Feedback::getFeedback(0, [
            "items" => $parameters
        ]);


    }

    public function set(Request $request)
    {

        if (!$request->has('items')) {
            return Feedback::getFeedback(204);
        }

        foreach ($request->input('items') as $item) {

            if (!array_key_exists('id', $item)) {
                return Feedback::getFeedback(205);
            }

            if (!array_key_exists('name', $item)) {
                return Feedback::getFeedback(201);
            }

            $name = $item['name'];
            $id = $item['id'];

            $parameter = Status::find($id);

            if ($parameter) {

                $parameter->name = $name;

                try {
                    $parameter->save();

                } catch (QueryException $ex) {
                    // В случае, если name не уникально
                    return Feedback::getFeedback(201);
                }

            } else {

                return Feedback::getFeedback(205);
            }

        }

        return Feedback::getFeedback(0);

    }
}
