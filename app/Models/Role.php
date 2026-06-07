<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];

    // Usuarios que tienen asignado este rol.
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'rol_id');
    }
}
