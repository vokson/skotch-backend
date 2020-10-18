<?php

namespace App\Mail;

use App\Models\ApiUser;
use App\Http\Controllers\SettingsController as Settings;
use App\Nodels\SenderFolder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SenderCreateFolderNotification extends Mailable
{
    use Queueable, SerializesModels;
    protected $folder;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SenderFolder $folder)
    {
        $this->folder = $folder;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $user = ApiUser::find($this->folder->owner);


        $subject = Settings::take('SENDER_FOLDER_NOTIFICATION_SUBJECT');

        return $this->from('noskov_as@niik.ru')
            ->subject('ВОЛГАФЕРТ. ОТПРАВКА. ' . $this->folder->name)
            ->view('emails.sender.folder_ready')
            ->with([
                'folderId' => $this->folder->id,
                'folderName' => $this->folder->name,
                'userSurname' => $user->name,
                'userName' => $user->surname,
                'folderIsReady' => $this->folder->is_ready
            ]);
    }
}
