<?php


Route::group(array('namespace' => 'Codificar\PushNotification\Http\Controllers'), function () {

    // Rotas do painel web
    Route::group(['prefix' => 'admin/libs/push_notification', 'middleware' => 'auth.admin'], function () {
        Route::get('/', array('as' => 'AdminGetPushNotificationSettings', 'uses' => 'PushNotificationController@getPushNotificationSettings'));
        Route::post('/save_settings', array('as' => 'AdminSavePushNotificationSettings', 'uses' => 'PushNotificationController@savePushNotificationSettings'));
    });

});



/**
 * Rota para permitir utilizar arquivos de traducao do laravel (dessa lib) no vue js
 */
Route::get('/libs/push_notification/lang.trans/{file}', function () {
    $fileNames = explode(',', Request::segment(4));
    $lang = config('app.locale');
    $files = array();
    foreach ($fileNames as $fileName) {
        array_push($files, __DIR__.'/../resources/lang/' . $lang . '/' . $fileName . '.php');
    }
    $strings = [];
    foreach ($files as $file) {
        $name = basename($file, '.php');
        $strings[$name] = require $file;
    }

    header('Content-Type: text/javascript');
    return ('window.lang = ' . json_encode($strings) . ';');
    exit();
})->name('assets.lang');