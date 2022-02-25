<?php


Route::group(array('namespace' => 'Codificar\PushNotification\Http\Controllers'), function () {

    // Rotas do painel web
    Route::group(['prefix' => '/admin/libs/push_notification', 'middleware' => 'auth.admin'], function () {
        Route::get('/', array('as' => 'AdminGetPushNotificationSettings', 'uses' => 'PushNotificationController@getPushNotificationSettings'));
        Route::post('/save_settings/ios', array('as' => 'AdminSavePushNotificationSettingsIos', 'uses' => 'PushNotificationController@savePushNotificationSettingsIos'));
        Route::post('/save_settings/android', array('as' => 'AdminSavePushNotificationSettingsAndroid', 'uses' => 'PushNotificationController@savePushNotificationSettingsAndroid'));
    });

});



/**
 * Rota para permitir utilizar arquivos de traducao do laravel (dessa lib) no vue js
 */
Route::get('/libs/push_notification/lang.trans/{file}', function () {
    
    app('debugbar')->disable();

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

    return response('window.lang = ' . json_encode($strings) . ';')
            ->header('Content-Type', 'text/javascript');
    
})->name('assets.lang');