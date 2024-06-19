<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Lib\Xrole\Services\JwtService;
use Symfony\Component\HttpFoundation\Response;

class CustomApiAuth
{
    protected $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function handle(Request $request, Closure $next)
    {
        // Extract the token from the request
        $token = $request->bearerToken();

        if ($token) {
            try {
                // Verify the token and extract the user ID
                $userId = $this->jwtService->verifyToken($token);

                // Authenticate the user by setting the user manually
                Auth::loginUsingId($userId);

                return $next($request);
            } catch (\Exception $e) {
                // Handle token verification errors (e.g., token expired, invalid token)
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }

        return response()->json(['error' => 'Token not provided'], 401);
    }
}
