<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\FeedbackController As Feedback;
use App\Models\ApiUser;
use App\Http\Controllers\SettingsController As Settings;

class UserController extends Controller
{
    public static function getUserId(Request $request) {
        $token = $request->input('access_token');
        $user = ApiUser::where('access_token', $token)->first();
        return $user->id;
    }

    public function set(Request $request)
    {
        $id = $request->input('id', null);
        $email = trim($request->input('email', ''));
        $surname = trim($request->input('surname', ''));
        $name = trim($request->input('name', ''));
        $role = trim($request->input('role', ''));
        $active = trim($request->input('active', ''));
        $permission_expression = trim($request->input('permission_expression', ''));

        if (!is_null($id)) {
            if (!ApiUser::where('id', '=', $id)->exists()) {
                return Feedback::getFeedback(501);
            }
        }

        if (is_null($id)) {
            $user = new ApiUser;
            $user->password = hash('sha256', Settings::take('DEFAULT_PASSWORD'));
            $user->access_token = uniqid();
        } else {
            $user = ApiUser::find($id);
        }

        if ($email == "") {
            return Feedback::getFeedback(502);
        }

        if ($surname == "") {
            return Feedback::getFeedback(503);
        }

        if ($name == "") {
            return Feedback::getFeedback(504);
        }

        if ($role == "") {
            return Feedback::getFeedback(505);
        }

        if ($active !== "0" && $active !== "1") {
            return Feedback::getFeedback(506);
        }

        if ($permission_expression == "") {
            return Feedback::getFeedback(507);
        }

        $user->email = $email;
        $user->surname = $surname;
        $user->name = $name;
        $user->role = $role;
        $user->active = $active;
        $user->permission_expression = $permission_expression;
        $user->save();

        return Feedback::getFeedback(0);
    }

    public function setDefaultPassword(Request $request)
    {
        $id = $request->input('id', null);

        if (!ApiUser::where('id', '=', $id)->exists()) {
            return Feedback::getFeedback(501);
        }

        $user = ApiUser::find($id);
        $user->password = hash('sha256', Settings::take('DEFAULT_PASSWORD'));
        $user->save();

        return Feedback::getFeedback(0);
    }

    public function delete(Request $request)
    {

        $id = $request->input('id', null);
        if (is_null($id)) {
            return Feedback::getFeedback(501);
        }

        $user = ApiUser::find($id);
        if (!$user->exists()) {
            return Feedback::getFeedback(501);
        }

        try {
            $user->delete();
        } catch (QueryException $e) {
            return Feedback::getFeedback(206);
        }


        return Feedback::getFeedback(0);
    }

    public function get(Request $request)
    {

        $email = $request->input('email', '');
        $surname = $request->input('surname', '');
        $name = $request->input('name', '');
        $role = $request->input('role', '');
        $active = $request->input('active', '');

        $items = DB::table('api_users')
            ->where('email', 'like', '%' . $email . '%')
            ->where('surname', 'like', '%' . $surname . '%')
            ->where('name', 'like', '%' . $name . '%')
            ->where('role', 'like', '%' . $role . '%')
            ->where('active', 'like', '%' . $active . '%')
            ->select(['id', 'name', 'surname', 'email', 'role', 'active', 'permission_expression'])
            ->orderBy('surname', 'asc')
            ->orderBy('name', 'asc')
            ->get();


        return Feedback::getFeedback(0, [
            'items' => $items->toArray(),
        ]);

    }


    public function changePassword(Request $request)
    {

        if (!$request->has('new_password')) {
            return Feedback::getFeedback(105);
        }

        $token = $request->input('access_token');
        $user = ApiUser::where('access_token', $token)->first();
        $user->password = $request->input('new_password');
        $user->save();

        return Feedback::getFeedback(0);

    }

    public static function getListOfRoles() {

        $roleList = DB::table('api_users')
            ->whereNotNull('role')
            ->groupBy('role')
            ->select(['role'])
            ->orderBy('role')
            ->get();

        $func = function ($item) {
            return $item->role;
        };

        return array_map($func, $roleList->toArray());
    }
}
