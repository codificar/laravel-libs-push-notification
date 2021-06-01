<?php

namespace Codificar\PushNotification\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SaveSettingsFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
	public function rules()
	{
		return [
            'ios_key_id'        => ['required', 'string'],
            'ios_team_id'       => ['required', 'string'],
            'package_user'      => ['required', 'string'],
            'package_provider'  => ['required', 'string'],
			'p8_file_upload'    => ['nullable', 'file']
		];
	}

	public function messages() {
		return [
            'ios_key_id.required'       => 'Key Id é necessário',
            'ios_team_id.required'      => 'Team Id é necessário',
            'package_user.required'     => 'User Package é necessário',
            'package_provider.required' => 'Provider Package Id é necessário',
            'p8_file_upload.file'       => 'O arquivo precisa ter a extensão .p8'
		];
	}
	
    /**
     * retorna um json caso a validação falhe.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json(
                [
                    'success' => false,
                    'errors' => $validator->errors()->all(),
                    'error_code' => \ApiErrors::REQUEST_FAILED
                ]
            )
        );
    }
}
