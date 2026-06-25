<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;

class GaleriaModal extends Model
{
    protected $table = 'galeria_modales';

    public $timestamps = false;

    protected $fillable = [
        'perfil_id',
        'imagen', // <-- Cambiado de imagen_base64 a imagen
    ];

   protected $appends = ['imagen_url'];

public function getImagenUrlAttribute() {
    return $this->imagen ? URL::to($this->imagen) : null;
}

    public function perfil(): BelongsTo
    {
        return $this->belongsTo(PerfilGrilla::class, 'perfil_id');
    }
}