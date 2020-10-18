<?php

namespace App\Http\Controllers;

use App\Models\Doc;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Http\Controllers\FeedbackController As Feedback;

class SettingsController extends Controller
{
    public static function take($name)
    {
        $parameter = Setting::where('name', $name)->first();

        if ($parameter) {
            return $parameter->value;
        }
    }

    public function get(Request $request)
    {
        $parameters = [];

        foreach (Setting::all() as $item) {
            $parameters[] = array_filter($item->toArray(), function ($k) {
                return ($k == 'name' || $k == 'value');
            }, ARRAY_FILTER_USE_KEY);
        }

        return Feedback::getFeedback(0, [
            "items" => $parameters
        ]);


    }

    public static function save($name, $value)
    {

        $parameter = Setting::where('name', $name)->first();

        if ($parameter) {

            $parameter->fill([
                'name' => $name,
                'value' => $value
            ]);

            return $parameter->save();

        } else {

            return false;
        }
    }

    public function set(Request $request)
    {

        if (!$request->has('items')) {
            return Feedback::getFeedback(204);
        }

        foreach ($request->input('items') as $item) {

            if (!array_key_exists('name', $item)) {
                return Feedback::getFeedback(201);
            }

            if (!array_key_exists('value', $item)) {
                return Feedback::getFeedback(202);
            }

            $name = $item['name'];
            $value = $item['value'];

            if (!self::save($name, $value)) {
                return Feedback::getFeedback(201);
            }

        }

        return Feedback::getFeedback(0);

    }


}
