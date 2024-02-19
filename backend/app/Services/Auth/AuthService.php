<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Traits\AuthTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    use AuthTrait;

    public function login($credentials): array
    {
        if (!Auth::attempt($credentials)) {
            return $this->error('Invalid credentials', 422);
        }
        return $this->token($this->getPersonalAccessToken(),Auth::user()->id,200,null);
    }

    public function signup($credentials)
    {
        $user = User::create([
            'name' => $credentials['name'],
            'email' => $credentials['email'],
            'password' => Hash::make($credentials['password'])
        ]);
        Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']]);
        return $this->token($this->getPersonalAccessToken(), 200, $user->id,'User Created');
    }

    public function logout()
    {
        Auth::user()->token()->revoke();
        return $this->success('User Logged Out', 200);
    }

    public function getPersonalAccessToken()
    {
        return Auth::user()->createToken('Personal Access Token');
    }

}
