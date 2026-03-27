<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;
use RuntimeException;

class AuthService
{
    public function register(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        if ($user->role === 'student' && !empty($data['interest_ids'])) {
            $user->interests()->sync($data['interest_ids']);
        }

        $token = $this->guard()->login($user);

        return [
            'user' => $user->load('interests'),
            'token' => $token,
        ];
    }

    public function login(array $credentials)
    {
        if (! $token = $this->guard()->attempt($credentials)) {
            throw new \Exception('Email ou mot de passe incorrect.');
        }

        return [
            'user' => $this->guard()->user(),
            'token' => $token,
        ];
    }

    public function me()
    {
        /** @var User|null $user */
        $user = $this->guard()->user();

        if (! $user instanceof User) {
            throw new RuntimeException('Authenticated user not found.');
        }

        return $user->load('interests');
    }

    public function logout()
    {
        $this->guard()->logout();
    }

    public function refresh()
    {
        return $this->guard()->refresh();
    }

    private function guard(): JWTGuard
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        return $guard;
    }
}
