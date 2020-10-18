<?php

use Illuminate\Support\Facades\Route;

Route::post('/auth/login', 'ApiAuthController@login');
Route::post('/auth/login/token', 'ApiAuthController@loginByToken');
Route::post('/auth/check_token', 'ApiAuthController@isTokenValid');

Route::middleware(['auth.api.token'])->group(function () {

    Route::post('/auth/change_password', 'UserController@changePassword');

    Route::post('/settings/get', 'SettingsController@get');
    Route::post('/settings/set', 'SettingsController@set');

    // LOG
    Route::post('/logs/get', 'LogController@get');
    Route::post('/logs/get/last/articles', 'LogController@getLatestArticles');

    Route::middleware(['auth.log.edit', 'reg_exp.log.edit'])->group(function () {

        Route::middleware(['log.transmittal.record.create'])->group(function () {
            Route::post('/logs/set', 'LogController@set');
        });

        Route::middleware(['log.transmittal.record.delete'])->group(function () {
            Route::post('/logs/delete', 'LogController@delete');
        });

    });

    // LOG FILE
    Route::post('/logs/file/get', 'LogFileController@get');
    Route::post('/logs/file/download', 'LogFileController@download');
    Route::post('/logs/file/download/all', 'LogFileController@downloadAll');
    Route::post('/logs/clean/files/without/articles', 'LogFileController@clean');

    Route::middleware(['auth.log.file.edit', 'reg_exp.log.file.edit'])->group(function () {
        Route::post('/logs/file/upload', 'LogFileController@upload');
        Route::post('/logs/file/delete', 'LogFileController@delete');
    });

    // LOG NEW MESSAGE
    Route::middleware(['auth.log.new.message'])->group(function () {
        Route::post('/logs/new/message/switch', 'LogNewMessageController@set');
    });

    Route::post('/logs/new/message/count', 'LogNewMessageController@count');

    // STATUS
    Route::post('/statuses/get', 'StatusController@get');
    Route::post('/statuses/set', 'StatusController@set');
    Route::post('/statuses/delete', 'StatusController@delete');
    Route::post('/statuses/add', 'StatusController@add');

    // TITLE
    Route::post('/titles/get', 'TitleController@get');
    Route::post('/titles/set', 'TitleController@set');
    Route::post('/titles/delete', 'TitleController@delete');
    Route::post('/titles/history/get', 'TitleHistoryController@get');

    // USER
    Route::post('/users/get', 'UserController@get');
    Route::post('/users/set', 'UserController@set');
    Route::post('/users/set/default/password', 'UserController@setDefaultPassword');
    Route::post('/users/delete', 'UserController@delete');

    // DATABASE
    Route::post('/service/database/backup', 'ServiceController@getDatabaseBackup');
    Route::post('/service/info', 'ServiceController@info');

    // STATISTIC
    Route::post('/charts/logs/created/get', 'StatisticController@getItemsForLogChart');
    Route::post('/charts/titles/created/get', 'StatisticController@getItemsForTitleChart');
    Route::post('/charts/titles/status/get', 'StatisticController@getItemsForTitleStatusChart');
    Route::post('/charts/tq/status/get', 'StatisticController@getItemsForTqStatus');
    Route::post('/charts/storage/get', 'StatisticController@getItemsForStorageChart');
    Route::post('/charts/checked/drawings/get', 'StatisticController@getItemsForCheckedDrawingsChart');

    // CHECK FILES
    Route::post('/checker/file/upload', 'CheckedFileController@upload');
    Route::post('/checker/file/download', 'CheckedFileController@download');
    Route::post('/checker/file/download/all', 'CheckedFileController@downloadAll');

    // CHECK
    Route::post('/checker/get', 'CheckController@get');
    Route::middleware(['auth.checker.file.delete'])->group(function () {
        Route::post('/checker/delete', 'CheckController@delete');
    });

    // SENDER
    Route::post('/sender/folder/add', 'SenderFolderController@add');
    Route::post('/sender/folder/get', 'SenderFolderController@get');
    Route::post('/sender/folder/delete', 'SenderFolderController@delete');
    Route::post('/sender/folder/count', 'SenderFolderController@count');
    Route::post('/sender/folder/switch/ready', 'SenderFolderController@switch');

    // SENDER FILES
    Route::post('/sender/file/upload', 'SenderFileController@upload');
    Route::post('/sender/file/get', 'SenderFileController@get');
    Route::post('/sender/file/delete', 'SenderFileController@delete');
    Route::post('/sender/file/download', 'SenderFileController@download');
    Route::post('/sender/file/download/all', 'SenderFileController@downloadAll');

    //MERGE PDF
    Route::post('/merge/pdf/get', 'MergePdfController@get');
    Route::post('/merge/pdf/clean', 'MergePdfController@clean');
    Route::post('/merge/pdf/set/main/name', 'MergePdfController@setMainName');
    Route::post('/merge/pdf/file/upload', 'MergePdfController@upload');
    Route::post('/merge/pdf/file/download', 'MergePdfController@download');

    //RATING
    Route::post('/checker/rating/get', 'StatisticController@getItemsForCheckerRatingChart');

    // USER SETTINGS
    Route::post('/settings/user/get', 'UserSettingsController@get');
    Route::post('/settings/user/set', 'UserSettingsController@set');

    // TASKS
    Route::post('/task/create', 'TaskController@create');

    // DOCS
    Route::post('/docs/edit/get', 'DocsController@getListOfTransmittal');
    Route::post('/docs/edit/set', 'DocsController@saveListOfTransmittal');
    Route::post('/docs/edit/add', 'DocsController@addNewDocumentToTransmittal');
    Route::post('/docs/edit/delete', 'DocsController@deleteDocumentFromTransmittal');
    Route::post('/docs/edit/file/upload', 'DocsController@upload');
    Route::post('/docs/update/priority/indexes', 'DocsController@updatePriorityIndexes');
    Route::post('/docs/search/get', 'DocsController@search');


    // COUNT
    Route::post('/counts', 'CountController@get');


    // ACTION
    Route::post('/action/set', 'ActionController@set');
    Route::post('/action/get', 'ActionController@get');

});
