<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Traits\AuthTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    use AuthTrait;

    public function login($credentials)
    {
        if (!Auth::attempt($credentials)) {
            return $this->error('Invalid credentials', 401);
        }
        return $this->token($this->getPersonalAccessToken());
    }

    public function signup($credentials)
    {
        User::create([
            'name' => $credentials['name'],
            'email' => $credentials['email'],
            'password' => Hash::make($credentials['password'])
        ]);
        Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']]);
        return $this->token($this->getPersonalAccessToken(), 'User Created', 201);
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
