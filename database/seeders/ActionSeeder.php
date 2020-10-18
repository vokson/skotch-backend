<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Action;

class ActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin_urls = [
            'auth/login',
            'auth/login/token',
            'auth/check_token',
            'action/set',
            'action/get'
        ];

        $guest_urls = [
            'auth/login',
            'auth/login/token',
            'auth/check_token',
        ];

        $urls = [
            'auth/login',
            'auth/login/token',
            'auth/check_token',
            'action/set',
            'action/get',
            'auth/change_password',
            "logs/get",
            "logs/set",
            "logs/delete",
            "titles/get",
            "titles/history/get",
            "users/get",
            "statuses/get",
            "logs/file/upload",
            "logs/file/download",
            "logs/file/download/all",
            "logs/file/get",
            "logs/file/delete",
            "logs/new/message/switch",
            "logs/new/message/count",
            "logs/get/last/articles",
            "charts/logs/created/get",
            "charts/titles/created/get",
            "charts/titles/status/get",
            "charts/tq/status/get",
            "charts/storage/get",
            "charts/checked/drawings/get",
            "checker/get",
            "checker/delete",
            "checker/rating/get",
            "checker/file/upload",
            "checker/file/download",
            "checker/file/download/all",
            "sender/folder/add",
            "sender/folder/get",
            "sender/folder/delete",
            "sender/folder/count",
            "sender/folder/switch/ready",
            "sender/file/upload",
            "sender/file/get",
            "sender/file/delete",
            "sender/file/download",
            "sender/file/download/all",
            "merge/pdf/get",
            "merge/pdf/clean",
            "merge/pdf/set/main/name",
            "merge/pdf/file/upload",
            "merge/pdf/file/download",
            'settings/user/get',
            'settings/user/set',
            'task/create',
            'docs/search/get',
            'counts',
            "titles/set",
            "titles/delete",
            "docs/edit/get",
            "docs/edit/set",
            "docs/edit/add",
            "docs/edit/delete",
            "docs/edit/file/upload",
            'docs/update/priority/indexes',
            "settings/get",
            "settings/set",
            "statuses/set",
            "statuses/add",
            "statuses/delete",
            "users/set",
            "users/set/default/password",
            "users/delete",
            'service/database/backup',
            'logs/clean/files/without/articles',
            'ADD_FIRST_RECORD_FOR_TRANSMITTAL',
            'DELETE_FIRST_RECORD_FOR_TRANSMITTAL',
            'DELETE_NON_OWNED_CHECK_FILE',
            'EDIT_NON_OWNED_LOG_RECORD',
            'EDIT_NON_OWNED_LOG_RECORD_FILE',
        ];

        foreach ($urls as $url) {
            $action = new Action(['name' => $url]);
            $action->save();
        }

        foreach ($admin_urls as $url) {
            $action = Action::create(['name' => $url, 'role' => 'admin']);
            $action->save();
        }

        foreach ($guest_urls as $url) {
            $action = Action::create(['name' => $url, 'role' => 'guest']);
            $action->save();
        }
    }
}
