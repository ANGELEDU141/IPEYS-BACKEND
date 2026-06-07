<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GaleriaModal extends Model
{
    protected $table = 'galeria_modales';

    public $timestamps = false;

    protected $fillable = [
        'perfil_id',
        'imagen_base64',
    ];

    // Perfil al que pertenece esta imagen.
    public function perfil(): BelongsTo
    {
        return $this->belongsTo(PerfilGrilla::class, 'perfil_id');
    }
}
