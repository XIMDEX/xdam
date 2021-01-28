<?php

namespace App\Traits;

use Carbon\Carbon;

trait AuthTrait{

	protected function token($personalAccessToken, $message = null, $code = 200)
	{
		$tokenData = [
			'access_token' => $personalAccessToken->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($personalAccessToken->token->expires_at)->toDateTimeString()
		];

		return $this->success($tokenData, $message, $code);
	}

    protected function success($data, $message = null, $code = 200)
	{
		return [
			'status'=> 'Success',
			'message' => $message,
			'data' => $data,
            'status_code' => $code
        ];
	}

	protected function error($message = null, $code)
	{
		return [
			'status'=>'Error',
			'message' => $message,
			'data' => null,
            'status_code' => $code
        ];
	}

}
