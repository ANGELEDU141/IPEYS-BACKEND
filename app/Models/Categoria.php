<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];

    // Perfiles clasificados dentro de esta categoria.
    public function perfiles(): HasMany
    {
        return $this->hasMany(PerfilGrilla::class, 'categoria_id');
    }
}
