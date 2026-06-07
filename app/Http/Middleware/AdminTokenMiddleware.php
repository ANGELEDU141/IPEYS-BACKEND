<?php

namespace App\Http\Middleware;

use App\Services\TokenService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminTokenMiddleware
{
    public function __construct(private TokenService $tokenService)
    {
    }

    // Valida Bearer token y confirma que pertenece a un administrador.
    public function handle(Request $request, Closure $next): Response
    {
        $apiToken = $this->tokenService->findActiveToken($request->bearerToken());

        if (!$apiToken) {
            return response()->json(['message' => 'Token requerido o invalido'], 401);
        }

        if ($apiToken->user->role?->nombre !== 'admin') {
            return response()->json(['message' => 'Permisos insuficientes'], 403);
        }

        $request->attributes->set('api_token', $apiToken);
        $request->attributes->set('auth_user', $apiToken->user);

        return $next($request);
    }
}
