<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\URL;

class PerfilGrilla extends Model
{

 use SoftDeletes;
    protected $table = 'perfiles_grilla';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'logo',
         'pdf',
        'categoria_id',
        'creado_por',
        'direccion',
        'experiencia',
        'especializacion',
        'contacto',
        'locales',
        'link',
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

    // Accesor para el logo: convierte la ruta de BD en URL completa
 protected $appends = ['logo_url', 'pdf_url'];

public function getLogoUrlAttribute() {
    return $this->logo ? URL::to($this->logo) : null;
}

public function getPdfUrlAttribute()
{
    return $this->pdf
        ? URL::to($this->pdf)
        : null;
}

    // Imagenes que se muestran en el modal.
    public function galeria(): HasMany
    {
        return $this->hasMany(GaleriaModal::class, 'perfil_id');
    }
}
