<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:student,teacher'],
            'interest_ids' => ['nullable', 'array'],
            'interest_ids.*' => ['exists:interests,id'],
        ]);

        $result = $this->authService->register($validated);

        return response()->json([
            'message' => 'Utilisateur créé avec succès',
            'user' => $result['user'],
            'access_token' => $result['token'],
            'token_type' => 'bearer',
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            $result = $this->authService->login($validated);

            return response()->json([
                'message' => 'Connexion réussie',
                'user' => $result['user'],
                'access_token' => $result['token'],
                'token_type' => 'bearer',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 401);
        }
    }

    public function me()
    {
        return response()->json($this->authService->me());
    }

    public function logout()
    {
        $this->authService->logout();

        return response()->json([
            'message' => 'Déconnexion réussie',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'access_token' => $this->authService->refresh(),
            'token_type' => 'bearer',
        ]);
    }
}