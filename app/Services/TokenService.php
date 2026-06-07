<?php

namespace App\Services;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TokenService
{
    // Tiempo maximo de sesion: por defecto 24 horas.
    public function ttlMinutes(): int
    {
        return min((int) config('app.api_token_ttl_minutes', env('API_TOKEN_TTL_MINUTES', 1440)), 1440);
    }

    // Crea un token visible una sola vez y guarda solo su hash.
    public function createToken(User $user): array
    {
        $plainToken = Str::random(80);
        $expiresAt = now()->addMinutes($this->ttlMinutes());

        $apiToken = ApiToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => $expiresAt,
        ]);

        return [
            'plain_token' => $plainToken,
            'api_token' => $apiToken,
        ];
    }

    // Busca un token activo a partir del Bearer enviado por el frontend.
    public function findActiveToken(?string $plainToken): ?ApiToken
    {
        if (!$plainToken) {
            return null;
        }

        $apiToken = ApiToken::with('user.role')
            ->where('token_hash', hash('sha256', $plainToken))
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();

        return $apiToken?->isActive() ? $apiToken : null;
    }

    // Invalida un token para cerrar sesion.
    public function revoke(ApiToken $apiToken): void
    {
        $apiToken->update(['revoked_at' => now()]);
    }

    // Datos de caducidad para mostrar al administrador.
    public function sessionInfo(ApiToken $apiToken): array
    {
        $expiresAt = Carbon::parse($apiToken->expires_at);

        return [
            'expires_at' => $expiresAt->toISOString(),
            'expires_in_seconds' => max(now()->diffInSeconds($expiresAt, false), 0),
            'max_session_minutes' => $this->ttlMinutes(),
        ];
    }
}
