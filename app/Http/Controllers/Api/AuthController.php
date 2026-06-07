<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService)
    {
    }

    // Login exclusivo para administradores.
    public function login(Request $request): JsonResponse
    {
        $result = $this->authService->login($request->all());

        return response()->json($result['body'], $result['status']);
    }

    // Valida token activo y devuelve datos de sesion.
    public function validateToken(Request $request): JsonResponse
    {
        return response()->json($this->authService->validate($request->attributes->get('api_token')));
    }

    // Devuelve tiempo restante de sesion.
    public function session(Request $request): JsonResponse
    {
        return response()->json($this->authService->validate($request->attributes->get('api_token'))['session']);
    }

    // Cierra sesion invalidando el token actual.
    public function logout(Request $request): JsonResponse
    {
        return response()->json($this->authService->logout($request->attributes->get('api_token')));
    }
}
