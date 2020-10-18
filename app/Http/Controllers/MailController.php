<?php

namespace App\Http\Controllers;

use App\Models\ApiUser;
use App\Models\UserSetting;
use App\Mail\SenderCreateFolderNotification;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public static function sendNotification($name, $payload)
    {
        $letter = self::generateMailBody($name, $payload);

        if (is_null($letter)) {
            return;
        }

        Mail::to(self::getListOfEmails($name))
            ->queue($letter);

    }

    private static function generateMailBody($name, $payload) {
        $result = null;

        switch ($name) {
            case 'SEND_NOTIFICATION_FROM_SENDER':
                return new SenderCreateFolderNotification($payload);
            default:
                return null;
        }
    }

    private static function getListOfEmails($name) {

        $settings = UserSetting::where('name', $name)
            ->where('owner', '<>', 0)
            ->where('value', 1)
            ->get();

        $list = [];

        foreach ($settings as $item) {
            $user = ApiUser::find($item->owner);
            if ($user && $user->active == 1) {
                $list[] = $user->email;
            }
        }

        return $list;
    }
}
