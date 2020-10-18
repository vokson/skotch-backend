<?php

namespace App\Http\Controllers;

use App\Models\ApiUser;
use Illuminate\Http\Request;
use App\Http\Controllers\FeedbackController As Feedback;
use App\Http\Controllers\SettingsController As Settings;

class ApiAuthController extends Controller
{

    private static function isTokenAlive($timeOfLastVisit)
    {
        return (Settings::take('TOKEN_LIFE_TIME') > (time() - $timeOfLastVisit->timestamp));
    }

    public static function id(Request $request) {
        $token = $request->input('access_token');
        $user = ApiUser::where('access_token', $token)->first();
        return $user->id;
    }

    public static function getSurnameAndNameOfUserById($id) {
        $user = ApiUser::find($id);
        return $user->surname . ' ' . $user->name;
    }



    public function login(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        $user = ApiUser::where('email', $email)->where('password', $password)->first();

        if ($user && $user->active == true) {

            $token = bin2hex(random_bytes(30));
            $user->access_token = $token;
            $user->save();

            return Feedback::getFeedback(0, [
                'access_token' => $user->access_token,
                'name' => $user->name,
                'surname' => $user->surname,
                'role' => $user->role,
                'email' => $user->email,
                'id' => $user->id,
                'isDefaultPassword' =>
                    (hash('sha256', Settings::take('DEFAULT_PASSWORD')) === $user->password)
            ]);

        } else  return Feedback::getFeedback(101);

    }


    public function loginByToken(Request $request)
    {
        $token = $request->input('access_token');
        $user = ApiUser::where('access_token', $token)->first();

        if ($user && $user->active == true && $token != "") {

            if (!self::isTokenAlive($user->updated_at)) return Feedback::getFeedback(102);

            return Feedback::getFeedback(0, [
                'access_token' => $user->access_token,
                'name' => $user->name,
                'surname' => $user->surname,
                'role' => $user->role,
                'email' => $user->email,
                'id' => $user->id,
                'isDefaultPassword' =>
                    (hash('sha256', Settings::take('DEFAULT_PASSWORD')) === $user->password)
            ]);

        } else  return Feedback::getFeedback(102);

    }

    public static function isTokenValid(Request $request)
    {
        $token = $request->input('access_token');
        $user = ApiUser::where('access_token', $token)->first();

        if ($user && $token != "") {

            if (!self::isTokenAlive($user->updated_at)) return Feedback::getFeedback(102);

            return Feedback::getFeedback(0);
        }

        return Feedback::getFeedback(103);

    }

    public static function getUserByToken($token)
    {
        $user = ApiUser::where('access_token', $token)->first();

        if (is_null($user)) {
            $user = ApiUser::where('email', 'guest@mail.com')->first();
        }

        return $user;
    }


}
