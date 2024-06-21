<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
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
                $user = User::where('uuid', $userId['sub'])->first();

                if (!$user) {
                    // User not found by UUID, make an external request to get user data
                    $response = Http::withToken($token)->get('external-url-to-get-user-data');

                    if ($response->successful()) {
                        $userData = $response->json();

                        // Check if the email is already in use
                        $existingUser = User::where('email', $userData['email'])->first();

                        if ($existingUser) {
                            // Email is already in use, throw an error
                            throw new \Exception('Email is already in use.');
                        }

                        // Email is not in use, create the user
                        $user = new User($userData);
                        $user->save(); // Or any other logic you need to persist the user data
                        Auth::login($user); // Authenticate the newly created user
                    }
                }

                if ($user) {
                    Auth::loginUsingId($user->id);
                    return $next($request);
                } else {
                    // Handle case where user is not found or external request fails
                    return response()->json(['error' => 'User not found'], 404);
                }
            } catch (\Exception $e) {
                // Handle token verification errors (e.g., token expired, invalid token)
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }

        return response()->json(['error' => 'Token not provided'], 401);
    }
}
