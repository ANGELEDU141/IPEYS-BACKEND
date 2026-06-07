<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerfilGrilla extends Model
{
    protected $table = 'perfiles_grilla';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'logo_base64',
        'categoria_id',
        'creado_por',
        'created_at',
    ];

    // Categoria usada para clasificar el perfil.
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    // Usuario administrador que creo el perfil.
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    // Imagenes que se muestran en el modal.
    public function galeria(): HasMany
    {
        return $this->hasMany(GaleriaModal::class, 'perfil_id');
    }
}
