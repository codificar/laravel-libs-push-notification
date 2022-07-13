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
use Error;
use Exception;
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
		
		$audio_new_ride = Settings::findByKey('audio_new_ride');
		$audio_ride_cancelation = Settings::findByKey('audio_ride_cancelation');
		$audio_push_notification = Settings::findByKey('audio_push_notification');
		$audio_msg_provider = Settings::findByKey('audio_chat_provider_notification');
		$audio_msg_user = Settings::findByKey('audio_chat_user_notification');
		
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
					->with('audio_push_cancellation', $audio_push_cancellation)

					->with('audio_new_ride', $audio_new_ride)
					->with('audio_ride_cancelation', $audio_ride_cancelation)
					->with('audio_push_notification', $audio_push_notification)
					->with('audio_msg_provider', $audio_msg_provider)
					->with('audio_msg_user', $audio_msg_user);
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

		$errors = [];

		$audioNewRide = $this->saveAudioNewRide();
		$audioCancellationRide = $this->saveAudioCancellationRide();
		$audioPushNotify = $this->addAudioPushNotify();
		
		$audioChatProvider = $this->addAudioChatProvider();
		$audioChatUser = $this->addAudioChatUser();
		
		if(!$audioNewRide['success']) {
			$errors[] = $audioNewRide['error'];
		}

		if(!$audioCancellationRide['success']) {
			$errors[] = $audioCancellationRide['error'];
		}

		if(!$audioPushNotify['success']) {
			$errors[] = $audioPushNotify['error'];
		}
		
		if(!$audioChatProvider['success']) {
			$errors[] = $audioChatProvider['error'];
		}
		if(!$audioChatUser['success']) {
			$errors[] = $audioChatUser['error'];
		}

		$success = true;
		$error = false;

		if(count($errors) > 0) {
			$success = false;
			$error = true;
		}

		// Return data
		$data = array(
			"success" => $success,
			"error" => $error,
			"errors" => $errors
		);

		return new SaveSettingsResource($data);

	}
	public function saveChatSettings() {

		$errors = [];

		$audioMsgProvider = $this->saveAudioMsgProvider();
		$audioMsgUser = $this->saveAudioMsgUser();
		
		if(!$audioMsgProvider['success']) {
			$errors[] = $audioMsgProvider['error'];
		}

		if(!$audioMsgUser['success']) {
			$errors[] = $audioMsgUser['error'];
		}
		$success = true;
		$error = false;

		if(count($errors) > 0) {
			$success = false;
			$error = true;
		}

		// Return data
		$data = array(
			"success" => $success,
			"error" => $error,
			"errors" => $errors
		);

		return new SaveSettingsResource($data);

	}

	protected function saveAudioMsgProvider() {
		if(Input::hasFile('audio_msg_provider')) {
			try {
				// Upload File
				$file = Input::file('audio_msg_provider');
				$file_name = 'chat_provider_' . Str::random(10);
				$ext  = $file->getClientOriginalExtension();
				$size = round( $file->getSize() / 1000 );
	
				if($ext == "mp3" && $size < 100) {
					$file->move(public_path() . "/uploads/audio/", $file_name . "." . $ext);
					$local_url = $file_name . "." . $ext;
	
					// salva no s3 se for o caso
					upload_to_s3($file_name, $local_url);
	
					$audio_msg_provider = asset_url() . '/uploads/audio/' . $local_url;
	
					///salvar url no banco de dados.
					Settings::updateOrCreate(['key' => 'audio_chat_provider_notification'], ['key' => 'audio_chat_provider_notification', 'value' => $audio_msg_provider]);
				}
				return ['success' => true, 'error' => false];
			} catch (Exception $e) {
				return ['success' => false, 'error' => $e->getMessage()];
			} catch (Error $e) {
				\Log::error($e->getMessage());
				return ['success' => false, 'error' => $e->getMessage()];
			}
		} else {
			return ['success' => true];
		}
	}

	protected function saveAudioMsgUser() {
		if(Input::hasFile('audio_msg_user')) {
			try {
				// Upload File
				$file = Input::file('audio_msg_user');
				$file_name = 'chat_user_' . Str::random(10);
				$ext  = $file->getClientOriginalExtension();
				$size = round( $file->getSize() / 1000 );
	
				if($ext == "mp3" && $size < 100) {
					$file->move(public_path() . "/uploads/audio/", $file_name . "." . $ext);
					$local_url = $file_name . "." . $ext;
	
					// salva no s3 se for o caso
					upload_to_s3($file_name, $local_url);
	
					$audio_msg_user = asset_url() . '/uploads/audio/' . $local_url;
	
					///salvar url no banco de dados.
					Settings::updateOrCreate(['key' => 'audio_chat_user_notification'], ['key' => 'audio_chat_user_notification', 'value' => $audio_msg_user]);
				}
				return ['success' => true, 'error' => false];
			} catch (Exception $e) {
				return ['success' => false, 'error' => $e->getMessage()];
			} catch (Error $e) {
				\Log::error($e->getMessage());
				return ['success' => false, 'error' => $e->getMessage()];
			}
		} else {
			return ['success' => true];
		}
	}

	protected function saveAudioNewRide() {
		if(Input::hasFile('audio_new_ride')) {
			try {
				// Upload File
				$file = Input::file('audio_new_ride');
				$file_name = 'new_ride_' . Str::random(10);
				$ext  = $file->getClientOriginalExtension();
				$size = round( $file->getSize() / 1000 );
	
				if($ext == "mp3" && $size < 100) {
					$file->move(public_path() . "/uploads/audio/", $file_name . "." . $ext);
					$local_url = $file_name . "." . $ext;
	
					// salva no s3 se for o caso
					upload_to_s3($file_name, $local_url);
	
					$audio_new_ride = asset_url() . '/uploads/audio/' . $local_url;
	
					///salvar url no banco de dados.
					Settings::updateOrCreate(['key' => 'audio_url'], ['key' => 'audio_url', 'value' => $audio_new_ride]);
					Settings::updateOrCreate(['key' => 'audio_beep_url'], ['key' => 'audio_beep_url', 'value' => $audio_new_ride]);
					Settings::updateOrCreate(['key' => 'audio_new_ride'], ['key' => 'audio_new_ride', 'value' => $audio_new_ride]);
				}
				return ['success' => true, 'error' => false];
			} catch (Exception $e) {
				return ['success' => false, 'error' => $e->getMessage()];
			} catch (Error $e) {
				\Log::error($e->getMessage());
				return ['success' => false, 'error' => $e->getMessage()];
			}
		} else {
			return ['success' => true];
		}
	}

	protected function saveAudioCancellationRide() {
		if(Input::hasFile('audio_ride_cancelation')) {
			try {
				// Upload File
				$file = Input::file('audio_ride_cancelation');
				$file_name = 'ride_cancelation_' . Str::random(10);
				$ext  = $file->getClientOriginalExtension();
				$size = round( $file->getSize() / 1000 );
	
				if($ext == "mp3" && $size < 100) {
					$file->move(public_path() . "/uploads/audio/", $file_name . "." . $ext);
					$local_url = $file_name . "." . $ext;
	
					// salva no s3 se for o caso
					upload_to_s3($file_name, $local_url);
	
					$audio_cancelation_ride = asset_url() . '/uploads/audio/' . $local_url;
	
					///salvar url no banco de dados.
					Settings::updateOrCreate(['key' => 'audio_push_cancellation'], ['key' => 'audio_push_cancellation', 'value' => $audio_cancelation_ride]);
					Settings::updateOrCreate(['key' => 'audio_ride_cancelation'], ['key' => 'audio_ride_cancelation', 'value' => $audio_cancelation_ride]);
				}
				return ['success' => true, 'error' => false];
			} catch (Exception $e) {
				return ['success' => false, 'error' => $e->getMessage()];
			} catch (Error $e) {
				\Log::error($e->getMessage());
				return ['success' => false, 'error' => $e->getMessage()];
			}
		} else {
			return ['success' => true];
		}
	}

	protected function addAudioPushNotify() {

		if (Input::hasFile('audio_push_notification')) {			
			try {
				// Upload File
				$file = Input::file('audio_push_notification');
				$file_name = 'push_notify_'. Str::random(10);
				$ext  = $file->getClientOriginalExtension();
				$size = round( $file->getSize() / 1000 );
	
				if ($ext == "mp3" && $size < 100) {
	
					$file->move(public_path() . "/uploads/audio//", $file_name . "." . $ext);
					$local_url = $file_name . "." . $ext;
	
					// salva no s3 se for o caso
					upload_to_s3($file_name, $local_url);
	
					$audio_push_notify = asset_url() . "/uploads/audio//" . $local_url;
	
					///salvar url no banco de dados.
					Settings::updateOrCreate(['key' => 'audio_push'], ['key' => 'audio_push', 'value' => $audio_push_notify]);
					Settings::updateOrCreate(['key' => 'audio_push_notification'], ['key' => 'audio_push_notification', 'value' => $audio_push_notify]);

				}
				return ['success' => true, 'error' => false];
			} catch (Exception $e) {
				return ['success' => false, 'error' => $e->getMessage()];
			} catch (Error $e) {
				\Log::error($e->getMessage());
				return ['success' => false, 'error' => $e->getMessage()];
			}
		} else {
			return ['success' => true];
		}
	}

	protected function addAudioChatProvider() {

		if (Input::hasFile('audio_msg_provider')) {			
			try {
				// Upload File
				$file = Input::file('audio_msg_provider');
				$file_name = 'chat_provider_'. Str::random(10);
				$ext  = $file->getClientOriginalExtension();
				$size = round( $file->getSize() / 1000 );
	
				if ($ext == "mp3" && $size < 100) {
	
					$file->move(public_path() . "/uploads/audio//", $file_name . "." . $ext);
					$local_url = $file_name . "." . $ext;
	
					// salva no s3 se for o caso
					upload_to_s3($file_name, $local_url);
	
					$audio_chat_provider = asset_url() . "/uploads/audio//" . $local_url;
	
					///salvar url no banco de dados.
					Settings::updateOrCreate(['key' => 'audio_chat_provider_notification'], ['key' => 'audio_chat_provider_notification', 'value' => $audio_chat_provider]);

				}
				return ['success' => true, 'error' => false];
			} catch (Exception $e) {
				return ['success' => false, 'error' => $e->getMessage()];
			} catch (Error $e) {
				\Log::error($e->getMessage());
				return ['success' => false, 'error' => $e->getMessage()];
			}
		} else {
			return ['success' => true];
		}
	}

	protected function addAudioChatUser() {

		if (Input::hasFile('audio_msg_user')) {			
			try {
				// Upload File
				$file = Input::file('audio_msg_user');
				$file_name = 'chat_user_'. Str::random(10);
				$ext  = $file->getClientOriginalExtension();
				$size = round( $file->getSize() / 1000 );
	
				if ($ext == "mp3" && $size < 100) {
	
					$file->move(public_path() . "/uploads/audio//", $file_name . "." . $ext);
					$local_url = $file_name . "." . $ext;
	
					// salva no s3 se for o caso
					upload_to_s3($file_name, $local_url);
	
					$audio_chat_user = asset_url() . "/uploads/audio//" . $local_url;
	
					///salvar url no banco de dados.
					Settings::updateOrCreate(['key' => 'audio_chat_user_notification'], ['key' => 'audio_chat_user_notification', 'value' => $audio_chat_user]);

				}
				return ['success' => true, 'error' => false];
			} catch (Exception $e) {
				return ['success' => false, 'error' => $e->getMessage()];
			} catch (Error $e) {
				\Log::error($e->getMessage());
				return ['success' => false, 'error' => $e->getMessage()];
			}
		} else {
			return ['success' => true];
		}
	}


	private function updateSetting($key, $value) {
		Settings::where('key', $key)->first()->update(['value' => $value]);
	}
}