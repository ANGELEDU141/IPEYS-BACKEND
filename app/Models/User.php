<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user',
        'password',
        'rol_id',
    ];

    protected $hidden = [
        'password',
    ];

    // Relacion del usuario con su rol.
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'rol_id');
    }

    // Perfiles creados por el usuario administrador.
    public function perfiles(): HasMany
    {
        return $this->hasMany(PerfilGrilla::class, 'creado_por');
    }

    // Tokens de API creados para sesiones administrativas.
    public function tokens(): HasMany
    {
        return $this->hasMany(ApiToken::class);
    }
}
