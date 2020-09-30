<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    // generic method to return responses from the api
    public function response($data, $error = null, $statusCode = 200)
    {
        $statusCode = $statusCode != 0 ? $statusCode : 500;
        $result = is_null($error) ? 'data' : 'message';

        return response()->json([
            $result => $data,
            'errors' => $error
        ], $statusCode);
    }
}
