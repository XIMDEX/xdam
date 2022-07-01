<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Traits\AuthTrait;
use App\Services\ExternalApis\KakumaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    use AuthTrait;

    private KakumaService $kakumaService;

    public function __construct(KakumaService $kakumaService)
    {
        $this->kakumaService = $kakumaService;
    }

    public function login($credentials): array
    {
        if (!Auth::attempt($credentials)) {
            return $this->error('Invalid credentials', 422);
        }
        return $this->token($this->getPersonalAccessToken(), null, 200, Auth::user()->id);
    }

    public function signup($credentials)
    {
        $user = User::create([
            'name' => $credentials['name'],
            'email' => $credentials['email'],
            'password' => Hash::make($credentials['password'])
        ]);
        Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']]);
        return $this->token($this->getPersonalAccessToken(), 'User Created', 200, $user->id);
    }

    public function logout()
    {
        Auth::user()->token()->revoke();
        return $this->success('User Logged Out', 200);
    }

    /**
     * @TODO Fix potential SECURITY ISSUE because of using loginAsSuperAdmin
     */
    public function generateKakumaToken()
    {
        return $this->kakumaService->loginAsSuperAdmin();
    }

    public function getPersonalAccessToken()
    {
        return Auth::user()->createToken('Personal Access Token');
    }

}
