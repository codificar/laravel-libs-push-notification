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

use Illuminate\Support\Str;

use Input, Validator, View, Response, Session;
use Settings;

class PushNotificationController extends Controller {

	public function getPushNotificationSettings(){

		$ios_key_id = Settings::findByKey('ios_key_id');
		$ios_team_id = Settings::findByKey('ios_team_id');
		$ios_package_user = Settings::findByKey('ios_package_user');
		$ios_package_provider = Settings::findByKey('ios_package_provider');
		$ios_auth_token_file_name = Settings::findByKey('ios_auth_token_file_name');
		$gcm_browser_key = Settings::findByKey('gcm_browser_key');
		$audio_push_url = Settings::findByKey('audio_push');
		$audio_beep_url = Settings::findByKey('audio_beep_url');

		if(!$audio_beep_url){
			$audio_beep_url = Settings::findByKey('audio_url');
			$audio_beep_url = $audio_beep_url->value ?? '';
		}
		$audio_push_cancellation = Settings::findByKey('audio_push_cancellation');

		$ios_p8url = null;
		if($ios_auth_token_file_name) {
			$ios_p8url = storage_path() . "/app/ios_push/" . $ios_auth_token_file_name . ".p8";
		}
		
		return View::make('push_notification::settings')
					->with('ios_p8url', $ios_p8url)
					->with('ios_key_id', $ios_key_id)
					->with('ios_team_id', $ios_team_id)
					->with('package_user', $ios_package_user)
					->with('package_provider', $ios_package_provider)
					->with('gcm_browser_key', $gcm_browser_key)
					->with('audio_push_url', $audio_push_url)
					->with('audio_beep_url', $audio_beep_url)
					->with('audio_url', $audio_beep_url)
					->with('audio_push_cancellation', $audio_push_cancellation);
	}

    public function savePushNotificationSettingsIos(SaveSettingsFormRequest $request) {

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
				$file_path = storage_path() . "/app/ios_push/";
				$dir_file = $file_path . $ios_auth_token_file_name;

				//check if path apps existis (the function file_exists check path too)
				if (!file_exists(storage_path() . "/app")) {
					mkdir(storage_path() . "/app", 0777, true);
				}
				if (!file_exists(storage_path() . "/app/ios_push")) {
					mkdir(storage_path() . "/app/ios_push", 0777, true);
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


	public function savePushNotificationSettingsAndroid() {

		$gcm = Input::get('gcm_browser_key');
		$this->updateSetting('gcm_browser_key', $gcm ? $gcm : '');

		$this->saveAudioCancellationPush();
		$this->addAudioPush();
		$this->saveAudioAlert();

		// Return data
		$data = array(
			"success" => true,
			"error" => false
		);

		return new SaveSettingsResource($data);

	}

	protected function saveAudioAlert() {
		if(Input::hasFile('audio_url') || Input::hasFile('audio_beep_url')) {

			// Upload File
			$file = Input::file('audio_url');
			if(Input::hasFile('audio_beep_url')) {
				$file = Input::file('audio_beep_url');
			}
			$file_name = Str::random(10);
			$ext  = $file->getClientOriginalExtension();
			$size = round( $file->getSize() / 1000 );

			if($ext == "mp3" && $size < 100) {
				$file->move(public_path() . "/uploads/audio/", $file_name . "." . $ext);
				$local_url = $file_name . "." . $ext;

				// salva no s3 se for o caso
				upload_to_s3($file_name, $local_url);

				$audio_beep_url = asset_url() . '/uploads/audio/' . $local_url;

				///salvar url no banco de dados.
				if(Input::hasFile('audio_beep_url')) {
					Settings::updateOrCreate(['key' => 'audio_beep_url'], ['key' => 'audio_beep_url', 'value' => $audio_beep_url]);
				} else {
					Settings::updateOrCreate(['key' => 'audio_url'], ['key' => 'audio_url', 'value' => $audio_beep_url]);
				}
			}
		}
	}

	protected function saveAudioCancellationPush() {
		if(Input::hasFile('audio_push_cancellation')) {

			// Upload File
			$file = Input::file('audio_push_cancellation');
			$file_name = Str::random(10);
			$ext  = $file->getClientOriginalExtension();
			$size = round( $file->getSize() / 1000 );

			if($ext == "mp3" && $size < 100) {
				$file->move(public_path() . "/uploads/audio/", $file_name . "." . $ext);
				$local_url = $file_name . "." . $ext;

				// salva no s3 se for o caso
				upload_to_s3($file_name, $local_url);

				$audio_beep_url = asset_url() . '/uploads/audio/' . $local_url;

				///salvar url no banco de dados.
				Settings::updateOrCreate(['key' => 'audio_push_cancellation'], ['key' => 'audio_push_cancellation', 'value' => $audio_beep_url]);
			}
		}
		if(Input::hasFile('audio_ride_cancellation')) {

			// Upload File
			$file = Input::file('audio_ride_cancellation');
			$file_name = 'audio_ride_cancellation' . Str::random(10);
			$ext  = $file->getClientOriginalExtension();
			$size = round( $file->getSize() / 1000 );

			if($ext == "mp3" && $size < 100) {
				$file->move(public_path() . "/uploads/audio/", $file_name . "." . $ext);
				$local_url = $file_name . "." . $ext;

				// salva no s3 se for o caso
				upload_to_s3($file_name, $local_url);

				$audio_beep_url = asset_url() . '/uploads/audio/' . $local_url;

				///salvar url no banco de dados.
				Settings::updateOrCreate(['key' => 'audio_ride_cancellation'], ['key' => 'audio_ride_cancellation', 'value' => $audio_beep_url]);
			}
		}

	}

	protected function addAudioPush() {
		if (Input::hasFile('audio_push')) {

			// Upload File
			$file = Input::file('audio_push');
			$file_name = Str::random(10);
			$ext  = $file->getClientOriginalExtension();
			$size = round( $file->getSize() / 1000 );

			if ($ext == "mp3" && $size < 100) {

				$file->move(public_path() . "/uploads/audio//", $file_name . "." . $ext);
				$local_url = $file_name . "." . $ext;

				// salva no s3 se for o caso
				upload_to_s3($file_name, $local_url);

				$audio_beep_url = asset_url() . "/uploads/audio//" . $local_url;

				///salvar url no banco de dados.
				Settings::updateOrCreate(['key' => 'audio_push'], ['key' => 'audio_push', 'value' => $audio_beep_url]);

			}
		}

	}


	private function updateSetting($key, $value) {
		Settings::where('key', $key)->first()->update(['value' => $value]);
	}
}