<?php

namespace App\Traits;

use Carbon\Carbon;

trait AuthTrait{

	protected function token($personalAccessToken, $message = null, $code = 200): array
	{
		$tokenData = [
			'access_token' => $personalAccessToken->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($personalAccessToken->token->expires_at)->toDateTimeString()
		];

		return $this->success($tokenData, $message, $code);
	}

    protected function success($data, $message = null, $code = 200): array
	{
		return [
            'code' => $code,
			'message' => $message,
			'data' => $data,
        ];
	}

	protected function error($message = null, $code): array
	{
        return [
            'code' => $code,
			'error' => $message,
        ];
	}

}
