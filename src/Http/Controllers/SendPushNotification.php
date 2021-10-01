<?php

namespace Codificar\PushNotification\Http\Controllers;

use App\Http\Controllers\Controller;
use Settings;

class SendPushNotification extends Controller {

    /**
	 * @param holder - 'user' or 'provider'
	 * @param device_token
     * @param title
     * @param msg
     * @param payload
	 */
	public static function sendIosPush($holder, $device_token, $title, $msg, $payload) {
        $ios_key_id = Settings::findByKey('ios_key_id');
        $ios_team_id = Settings::findByKey('ios_team_id');
        $ios_auth_token_file_name = Settings::findByKey('ios_auth_token_file_name');

        if($holder == 'user') {
            $package = Settings::findByKey('ios_package_user');
        } else {
            $package = Settings::findByKey('ios_package_provider');
        }

        $iosPush = new IosPush();
        $dir_file = storage_path() . "/apps/ios_push/" . $ios_auth_token_file_name;
        $dir_file_jwt = $dir_file . ".jwt.txt";

        if(!$ios_key_id || !$ios_team_id || !$ios_auth_token_file_name || !$package || !file_exists($dir_file_jwt)) {
            \Log::error("iOS push notification error: please complete the ios notification setup in the admin panel");
        } else {
            $token_jwt = file_get_contents($dir_file . ".jwt.txt");
            //envia o push notification
            $pushStatus = $iosPush->send($token_jwt, $package, $device_token, $title, $msg);
            //se o token esta expirado, atualiza o token e tenta enviar o push novamente
            if($pushStatus['msg'] == 'ExpiredProviderToken') {
                $private_key_pem_str = "file://" . $dir_file . ".pem";
                $token_jwt = $iosPush->createToken($ios_team_id, $ios_key_id, $private_key_pem_str);
                //salva o arquivo txt com o token
                $fp = fopen($dir_file_jwt, "wb");
                fwrite($fp,$token_jwt);
                fclose($fp);

                //tenta enviar o push novamente (com o token atualizado)
                $pushStatus = $iosPush->send($token_jwt, $package, $device_token, $title, $msg);
            }
        }

          
  
	}

    public function sendAndroidPush($holder, $device_token, $title, $msg, $payload) {
		
	}

}