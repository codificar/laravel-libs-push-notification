<?php

namespace Codificar\PushNotification\Http\Controllers;

use App\Http\Controllers\Controller;

// Importar models
use Codificar\PushNotification\Models\LibModel;

//FormRequest
use Codificar\PushNotification\Http\Requests\SaveSettingsFormRequest;

//Resource
use Codificar\PushNotification\Http\Resources\SaveSettingsResource;

use Carbon\Carbon;
use Auth;

use Input, Validator, View, Response, Session;
use Settings;

class PushNotificationController extends Controller {

	public function getPushNotificationSettings(){		

		$ios_key_id = Settings::findByKey('ios_key_id');
		$ios_team_id = Settings::findByKey('ios_team_id');
		$ios_package_user = Settings::findByKey('ios_package_user');
		$ios_package_provider = Settings::findByKey('ios_package_provider');
		$ios_auth_token_file_name = Settings::findByKey('ios_auth_token_file_name');

		$ios_p8url = null;
		if($ios_auth_token_file_name) {
			$ios_p8url = asset_url() . "/apps/ios_push/" . $ios_auth_token_file_name . ".p8";
		}
		return View::make('push_notification::settings')
					->with('ios_p8url', $ios_p8url)
					->with('ios_key_id', $ios_key_id)
					->with('ios_team_id', $ios_team_id)
					->with('package_user', $ios_package_user)
					->with('package_provider', $ios_package_provider);
	}

    public function savePushNotificationSettings(SaveSettingsFormRequest $request) {

		$this->updateSetting('ios_key_id', $request->ios_key_id);
		$this->updateSetting('ios_team_id', $request->ios_team_id);
		$this->updateSetting('ios_package_user', $request->package_user);
		$this->updateSetting('ios_package_provider', $request->package_provider);

		$p8_file_upload = $request->p8_file_upload;
		if($p8_file_upload) {
			$extension = pathinfo($p8_file_upload->getClientOriginalName(), PATHINFO_EXTENSION);

			//Verifica se o arquivo eh do formato correto (p8 ou P8)
			if(strtolower($extension) != "p8") {
				return Response::json(array('success' => false, 'errors' => ['Arquivo precisa ser .p8']), 200);
			} else {
				//pega o nome do arquivo
				$ios_auth_token_file_name = Settings::findByKey('ios_auth_token_file_name');
				if(!$ios_auth_token_file_name) { //se nao existe nome do arquivo, cria um novo
					$ios_auth_token_file_name = sha1(time() . rand());
					$this->updateSetting('ios_auth_token_file_name', $ios_auth_token_file_name);  // o nome do arquivo precisa ser aleatorio. Se fosse nome fixo, qualquer usuario poderia acessar o arquivo no navegador, pois esta salvo na pasta public
				}
				$file_path = public_path() . "/apps/ios_push/";
				$dir_file = $file_path . $ios_auth_token_file_name;

				//check if path apps existis (the function file_exists check path too)
				if (!file_exists(public_path() . "/apps")) {
					mkdir(public_path() . "/apps", 0777, true);
				}
				if (!file_exists(public_path() . "/apps/ios_push")) {
					mkdir(public_path() . "/apps/ios_push", 0777, true);
				}


				$p8_file_upload->move($file_path, $ios_auth_token_file_name . ".p8");
				

				//convert p8 to pem file. the command is: openssl pkcs8 -nocrypt -in file.p8 -out file.pem
				exec("openssl pkcs8 -nocrypt -in " .$dir_file . ".p8" . " -out " .$dir_file . ".pem");				
				
				//cria o token jwt para enviar os push notifications
				$iosPush = new IosPush();
				$private_key_pem_str = "file://" . $dir_file . ".pem";
                $token_jwt = $iosPush->createToken($request->ios_team_id, $request->ios_key_id, $private_key_pem_str);
                //salva o arquivo txt com o token
                $fp = fopen($dir_file . ".jwt.txt", "wb");
                fwrite($fp, $token_jwt);
                fclose($fp);
			}
		}
        
		// Return data
		$data = array(
			"success" => true,
			"error" => false
		);

		return new SaveSettingsResource($data);
			
	}

	private function updateSetting($key, $value) {
		Settings::where('key', $key)->first()->update(['value' => $value]);
	}
}