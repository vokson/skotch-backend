<?php

namespace App\Http\Controllers;


class ExceptionFeedbackController extends Controller
{
    public static function success($feedback = [])
    {
        $feedback['success'] = 1;
        return json_encode($feedback);
    }

    public static function error(\Exception $e)
    {
        $feedback = [];
        $feedback['success'] = 0;
        $feedback['error'] = self::getCodeByException($e);
        $feedback['description'] = $e->getMessage();
        return json_encode($feedback);
    }

    private static function getCodeByException(\Exception $e)
    {
        // Action
        if ($e instanceof \App\Exceptions\Action\Validation\Role) {
            return 110;
        } elseif ($e instanceof \App\Exceptions\Action\Validation\Name) {
            return 111;
        } elseif ($e instanceof \App\Exceptions\Action\Validation\State) {
            return 112;
        } elseif ($e instanceof \App\Exceptions\Action\Validation\Items) {
            return 113;
        }

        // UNHANDLED EXCEPTION
        return 100;

    }

}
