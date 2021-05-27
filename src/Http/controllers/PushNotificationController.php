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

class PushNotificationController extends Controller {

	public function getPushNotificationSettings(){		

		return View::make('push_notification::settings')
					->with('package_user', ["br.com.teste"])
					->with('package_provider',  "br.com.teste");
	}

    public function savePushNotificationSettings(SaveSettingsFormRequest $request) {

        // $id = $request->id;

		$data = array(
			"success" => true,
			"error" => false
		);

        return new SaveSettingsResource($data);
	}
}