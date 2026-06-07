<?php

namespace App\Services;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(private TokenService $tokenService)
    {
    }

    // Login exclusivo para administradores.
    public function login(array $data): array
    {
        if (empty($data['user']) || empty($data['password'])) {
            return ['status' => 400, 'body' => ['message' => 'User y password son obligatorios']];
        }

        $user = User::with('role')->where('user', $data['user'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return ['status' => 401, 'body' => ['message' => 'Credenciales invalidas']];
        }

        if ($user->role->nombre !== 'admin') {
            return ['status' => 403, 'body' => ['message' => 'Solo administradores pueden iniciar sesion']];
        }

        $tokenData = $this->tokenService->createToken($user);

        return [
            'status' => 200,
            'body' => [
                'token' => $tokenData['plain_token'],
                'token_type' => 'Bearer',
                'expires_at' => $tokenData['api_token']->expires_at->toISOString(),
                'expires_in_seconds' => $this->tokenService->sessionInfo($tokenData['api_token'])['expires_in_seconds'],
                'user' => $this->publicUser($user),
            ],
        ];
    }

    // Valida token y devuelve datos de usuario/sesion.
    public function validate(ApiToken $apiToken): array
    {
        return [
            'valid' => true,
            'user' => $this->publicUser($apiToken->user),
            'session' => $this->tokenService->sessionInfo($apiToken),
        ];
    }

    // Cierra sesion invalidando el token actual.
    public function logout(ApiToken $apiToken): array
    {
        $this->tokenService->revoke($apiToken);

        return ['message' => 'Sesion cerrada correctamente'];
    }

    private function publicUser(User $user): array
    {
        return [
            'id' => $user->id,
            'user' => $user->user,
            'rol_id' => $user->rol_id,
            'role' => $user->role?->nombre,
        ];
    }
}
