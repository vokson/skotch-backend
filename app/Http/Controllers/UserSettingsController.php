<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserSetting;
use App\Http\Controllers\FeedbackController as Feedback;

class UserSettingsController extends Controller
{
    public function get(Request $request)
    {
        $defaultSettings = $this->getListOfSettingForUser(0); // 0 - default owner
        $userSettings = $this->getListOfSettingForUser(UserController::getUserId($request));

        $userAssocSettings = [];

        foreach ($userSettings as $item) {
            $userAssocSettings[$item['name']] = $item['value'];
        }

        foreach ($defaultSettings as &$item) {
            if (isset($userAssocSettings[$item['name']])) {
                $item['value'] = $userAssocSettings[$item['name']];
            }
        }

        return Feedback::getFeedback(0, [
            "items" => $defaultSettings,
        ]);


    }

    // return associative array [name] => [value]
    private function getListOfSettingForUser($userId)
    {
        $parameters = [];

        foreach (UserSetting::where('owner', $userId)->get() as $item) {
            $parameters[] = array_filter($item->toArray(), function ($k) {
                return (
                    $k == 'name' ||
                    $k == 'value' ||
                    $k == 'is_switchable' ||
                    $k == 'description_RUS' ||
                    $k == 'description_ENG'
                );
            }, ARRAY_FILTER_USE_KEY);

        }

        return $parameters;
    }

    public function set(Request $request)
    {

        if (!$request->has('items')) {
            return Feedback::getFeedback(203);
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

            if (!self::save($name, $value, UserController::getUserId($request))) {
                return Feedback::getFeedback(201);
            }

        }

        return Feedback::getFeedback(0);

    }

    public static function save($name, $value, $userId)
    {

        $parameter = UserSetting::where('name', $name)->where('owner', $userId)->first();

        if ($parameter) {
            $parameter->value = $value;
        } else {
            $parameter = new UserSetting([
                'name' => $name,
                'value' => $value,
                'owner' => $userId
            ]);
        }

        return $parameter->save();

    }
}
