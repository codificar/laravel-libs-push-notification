<?php

namespace Codificar\PushNotification\Http\Controllers;

use App\Http\Controllers\Controller;

class IosPush extends Controller {

	public function createToken($team_id, $key_id, $private_key_pem_str) {
		if (! function_exists('openssl_get_md_methods') || ! in_array('sha256', openssl_get_md_methods())) throw new \Exception('Requires openssl with sha256 support');

		$private_key = openssl_pkey_get_private($private_key_pem_str);
		if (! $private_key) throw new \Exception('Cannot decode private key');

		$msg = $this->base64url_encode(json_encode([ 'alg' => 'ES256', 'kid' => $key_id ])) . '.' . $this->base64url_encode(json_encode([ 'iss' => $team_id, 'iat' => time() ]));
		openssl_sign($msg, $der, $private_key, 'sha256');

		// DER unpacking from https://github.com/firebase/php-jwt
		$components = [];
		$pos = 0;
		$size = strlen($der);
		while ($pos < $size) {
			$constructed = (ord($der[$pos]) >> 5) & 0x01;
			$type = ord($der[$pos++]) & 0x1f;
			$len = ord($der[$pos++]);
			if ($len & 0x80) {
				$n = $len & 0x1f;
				$len = 0;
				while ($n-- && $pos < $size) $len = ($len << 8) | ord($der[$pos++]);
			}

			if ($type == 0x03) {
				$pos++;
				$components[] = substr($der, $pos, $len - 1);
				$pos += $len - 1;
			} else if (! $constructed) {
				$components[] = substr($der, $pos, $len);
				$pos += $len;
			}
		}
		foreach ($components as &$c) $c = str_pad(ltrim($c, "\x00"), 32, "\x00", STR_PAD_LEFT);
		$jwt = $msg . '.' . $this->base64url_encode(implode('', $components));

		return $jwt;
	}

	public function send($jwt, $app_bundle_id, $device_token, $title, $msg) {
		//send request to apple
		// open connection
		$http2ch = curl_init();
		curl_setopt($http2ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);

		// send push
		$jsonMsg = array(
			'aps' => array(
				'alert' => array(
					'title' => $title,
					'body' => $msg
				)
			)
		);
		$message = json_encode($jsonMsg);
		$http2_server = 'https://api.push.apple.com';
		$res = $this->sendHTTP2Push($http2ch, $http2_server, $app_bundle_id, $message, $device_token, $jwt);

		// close connection
		curl_close($http2ch);

		return $res;
	} 

	private function sendHTTP2Push($http2ch, $http2_server, $app_bundle_id, $message, $token, $jwt) {

		// url (endpoint)
		$url = "{$http2_server}/3/device/{$token}";
	
		// headers
		$headers = array(
			"apns-topic: {$app_bundle_id}",
			'Authorization: bearer ' . $jwt
		);
	
		// other curl options
		curl_setopt_array($http2ch, array(
			CURLOPT_URL => $url,
			CURLOPT_PORT => 443,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_POST => TRUE,
			CURLOPT_POSTFIELDS => $message,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_HEADER => 1
		));
	
		// go...
		$result = curl_exec($http2ch);
		if ($result === FALSE) {
			throw new Exception("Curl failed: " .  curl_error($http2ch));
		}

		$http_status = curl_getinfo($http2ch, CURLINFO_HTTP_CODE);

		if($http_status == 200) {
			return array(
				'status' => $http_status,
				'msg' => 'Success'
			);
		} 
		else {
			$header_size = curl_getinfo($http2ch, CURLINFO_HEADER_SIZE);
			$body = substr($result, $header_size);
			return array(
				'status' => $http_status,
				'msg' => json_decode($body)->reason
			);
			
		}
	}

	private function base64url_encode($binary_data) { return strtr(rtrim(base64_encode($binary_data), '='), '+/', '-_'); }
}