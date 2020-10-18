<?php

use App\Http\Controllers\ActionController;
use App\Http\Controllers\CheckController;
use App\Http\Controllers\CheckedFileController;
use App\Http\Controllers\CountController;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\LogFileController;
use App\Http\Controllers\LogNewMessageController;
use App\Http\Controllers\MergePdfController;
use App\Http\Controllers\SenderFileController;
use App\Http\Controllers\SenderFolderController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StatisticController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TitleController;
use App\Http\Controllers\TitleHistoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserSettingsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiAuthController;


Route::post('/auth/login', [ApiAuthController::class, 'login']);
Route::post('/auth/login/token', [ApiAuthController::class, 'loginByToken']);
Route::post('/auth/check_token', [ApiAuthController::class, 'isTokenValid']);

Route::middleware(['auth.api.token'])->group(function () {

    Route::post('/auth/change_password', [UserController::class, 'changePassword']);

    Route::post('/settings/get', [SettingsController::class, 'get']);
    Route::post('/settings/set', [SettingsController::class, 'set']);

    // LOG
    Route::post('/logs/get', [LogController::class, 'get']);
    Route::post('/logs/get/last/articles', [LogController::class, 'getLatestArticles']);

    Route::middleware(['auth.log.edit', 'reg_exp.log.edit'])->group(function () {

        Route::middleware(['log.transmittal.record.create'])->group(function () {
            Route::post('/logs/set', [LogController::class, 'set']);
        });

        Route::middleware(['log.transmittal.record.delete'])->group(function () {
            Route::post('/logs/delete', [LogController::class, 'delete']);
        });

    });

    // LOG FILE
    Route::post('/logs/file/get', [LogFileController::class, 'get']);
    Route::post('/logs/file/download', [LogFileController::class, 'download']);
    Route::post('/logs/file/download/all', [LogFileController::class, 'downloadAll']);
    Route::post('/logs/clean/files/without/articles', [LogFileController::class, 'clean']);

    Route::middleware(['auth.log.file.edit', 'reg_exp.log.file.edit'])->group(function () {
        Route::post('/logs/file/upload', [LogFileController::class, 'upload']);
        Route::post('/logs/file/delete', [LogFileController::class, 'delete']);
    });

    // LOG NEW MESSAGE
    Route::middleware(['auth.log.new.message'])->group(function () {
        Route::post('/logs/new/message/switch', [LogNewMessageController::class, 'set']);
    });

    Route::post('/logs/new/message/count', [LogNewMessageController::class, 'count']);

    // STATUS
    Route::post('/statuses/get', [StatusController::class, 'get']);
    Route::post('/statuses/set', [StatusController::class, 'set']);
    Route::post('/statuses/delete', [StatusController::class, 'delete']);
    Route::post('/statuses/add', [StatusController::class, 'add']);

    // TITLE
    Route::post('/titles/get', [TitleController::class, 'get']);
    Route::post('/titles/set', [TitleController::class, 'set']);
    Route::post('/titles/delete', [TitleController::class, 'delete']);
    Route::post('/titles/history/get', [TitleHistoryController::class, 'get']);

    // USER
    Route::post('/users/get', [UserController::class, 'get']);
    Route::post('/users/set', [UserController::class, 'set']);
    Route::post('/users/set/default/password', [UserController::class, 'setDefaultPassword']);
    Route::post('/users/delete', [UserController::class, 'delete']);

    // DATABASE
    Route::post('/service/database/backup', [ServiceController::class, 'getDatabaseBackup']);
    Route::post('/service/info', [ServiceController::class, 'info']);

    // STATISTIC
    Route::post('/charts/logs/created/get', [StatisticController::class, 'getItemsForLogChart']);
    Route::post('/charts/titles/created/get', [StatisticController::class, 'getItemsForTitleChart']);
    Route::post('/charts/titles/status/get', [StatisticController::class, 'getItemsForTitleStatusChart']);
    Route::post('/charts/tq/status/get', [StatisticController::class, 'getItemsForTqStatus']);
    Route::post('/charts/storage/get', [StatisticController::class, 'getItemsForStorageChart']);
    Route::post('/charts/checked/drawings/get', [StatisticController::class, 'getItemsForCheckedDrawingsChart']);

    // CHECK FILES
    Route::post('/checker/file/upload', [CheckedFileController::class, 'upload']);
    Route::post('/checker/file/download', [CheckedFileController::class, 'download']);
    Route::post('/checker/file/download/all', [CheckedFileController::class, 'downloadAll']);

    // CHECK
    Route::post('/checker/get', [CheckController::class, 'get']);
    Route::middleware(['auth.checker.file.delete'])->group(function () {
        Route::post('/checker/delete', [CheckController::class, 'delete']);
    });

    // SENDER
    Route::post('/sender/folder/add', [SenderFolderController::class, 'add']);
    Route::post('/sender/folder/get', [SenderFolderController::class, 'get']);
    Route::post('/sender/folder/delete', [SenderFolderController::class, 'delete']);
    Route::post('/sender/folder/count', [SenderFolderController::class, 'count']);
    Route::post('/sender/folder/switch/ready', [SenderFolderController::class, 'switch']);

    // SENDER FILES
    Route::post('/sender/file/upload', [SenderFileController::class, 'upload']);
    Route::post('/sender/file/get', [SenderFileController::class, 'get']);
    Route::post('/sender/file/delete', [SenderFileController::class, 'delete']);
    Route::post('/sender/file/download', [SenderFileController::class, 'download']);
    Route::post('/sender/file/download/all', [SenderFileController::class, 'downloadAll']);

    //MERGE PDF
    Route::post('/merge/pdf/get', [MergePdfController::class, 'get']);
    Route::post('/merge/pdf/clean', [MergePdfController::class, 'clean']);
    Route::post('/merge/pdf/set/main/name', [MergePdfController::class, 'setMainName']);
    Route::post('/merge/pdf/file/upload', [MergePdfController::class, 'upload']);
    Route::post('/merge/pdf/file/download', [MergePdfController::class, 'download']);

    //RATING
    Route::post('/checker/rating/get', [StatisticController::class, 'getItemsForCheckerRatingChart']);

    // USER SETTINGS
    Route::post('/settings/user/get', [UserSettingsController::class, 'get']);
    Route::post('/settings/user/set', [UserSettingsController::class, 'set']);

    // TASKS
    Route::post('/task/create', [TaskController::class, 'create']);

    // DOCS
    Route::post('/docs/edit/get', [DocsController::class, 'getListOfTransmittal']);
    Route::post('/docs/edit/set', [DocsController::class, 'saveListOfTransmittal']);
    Route::post('/docs/edit/add', [DocsController::class, 'addNewDocumentToTransmittal']);
    Route::post('/docs/edit/delete', [DocsController::class, 'deleteDocumentFromTransmittal']);
    Route::post('/docs/edit/file/upload', [DocsController::class, 'upload']);
    Route::post('/docs/update/priority/indexes', [DocsController::class, 'updatePriorityIndexes']);
    Route::post('/docs/search/get', [DocsController::class, 'search']);


    // COUNT
    Route::post('/counts', [CountController::class, 'get']);


    // ACTION
    Route::post('/action/set', [ActionController::class, 'set']);
    Route::post('/action/get', [ActionController::class, 'get']);

});
