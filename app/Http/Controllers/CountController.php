<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\FeedbackController as Feedback;

class CountController extends Controller
{
    public function get(Request $request)
    {
       $countOfNewMessages = LogNewMessageController::count($request);
       $countOfSenderFolders = SenderFolderController::count();
       $countOfNonCheckedDocs = CheckController::count();

        return Feedback::getFeedback(0, [
            'items' => [
                'new_messages' => $countOfNewMessages,
                'sender_folders' => $countOfSenderFolders,
                'non_checked_docs' => $countOfNonCheckedDocs
            ]
        ]);

    }
}
