<?php

namespace App\Http\Controllers;

use App\Models\SenderFile;
use App\Models\SenderFolder;
use Illuminate\Http\Request;
use App\Http\Controllers\FeedbackController As Feedback;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class SenderFolderController extends Controller
{
    function add(Request $request)
    {

        SenderFolder::create([
            'name' => trim($request->input('name', '')),
            'owner' => ApiAuthController::id($request)
        ]);

        return Feedback::getFeedback(0);
    }

    function get()
    {

        $items = DB::table('sender_folders')
            ->select(['id', 'name', 'owner', 'is_ready', 'created_at as date'])
            ->get();

        // Подменяем id на значения полей из других таблиц
        $items->transform(function ($item, $key) {
            $item->owner = ApiAuthController::getSurnameAndNameOfUserById($item->owner);
            return $item;
        });

        return Feedback::getFeedback(0, [
            'items' => $items->toArray(),
        ]);
    }

    function delete(Request $request)
    {
        $folder_id = intval($request->input('id', 0));

        $files = SenderFile::where('folder', $folder_id)->get();

        foreach ($files as $file) {
            if ((Storage::delete($file->server_name) === false) || ($file->delete() === false)) {
                return Feedback::getFeedback(603);
            }
        }

        return (SenderFolder::destroy($folder_id)) ? Feedback::getFeedback(0) : Feedback::getFeedback(901);
    }

    function switch (Request $request)
    {
        $id = intval($request->input('id', 0));
        $folder = SenderFolder::find($id);

        if (is_null($folder)) {
            return Feedback::getFeedback(901);
        }

        $folder->is_ready = ($folder->is_ready == 1) ? 0 : 1;
        $folder->save();

        MailController::sendNotification('SEND_NOTIFICATION_FROM_SENDER', $folder);

        return Feedback::getFeedback(0);
    }

    public static function count()
    {
        return SenderFolder::all()->count();
    }
}
