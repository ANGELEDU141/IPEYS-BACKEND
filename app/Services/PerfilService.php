<?php

namespace App\Services;

use App\Models\Categoria;
use Illuminate\Support\Facades\Log;
use App\Models\PerfilGrilla;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File; // <-- Importado para limpiar archivos físicos
use Illuminate\Http\UploadedFile; // <-- Importado para validar los archivos subidos

class PerfilService
{
    public function list(array $query)
    {
        $perPage = min(max((int) ($query['per_page'] ?? 12), 1), 50);

        $paginator = PerfilGrilla::query()
            ->select('perfiles_grilla.*')
           ->with(['categoria:id,nombre', 'galeria:id,perfil_id,imagen'])
            ->join('categorias', 'categorias.id', '=', 'perfiles_grilla.categoria_id')
            ->when(!empty($query['search']), function ($builder) use ($query) {
                $search = '%' . $query['search'] . '%';

                $builder->where(function ($inner) use ($search) {
                    $inner->where('perfiles_grilla.nombre', 'like', $search)
                        ->orWhere('perfiles_grilla.descripcion', 'like', $search)
                        ->orWhere('perfiles_grilla.direccion', 'like', $search)
                        ->orWhere('perfiles_grilla.experiencia', 'like', $search)
                        ->orWhere('perfiles_grilla.especializacion', 'like', $search)
                        ->orWhere('perfiles_grilla.contacto', 'like', $search)
                        ->orWhere('perfiles_grilla.locales', 'like', $search)
                        ->orWhere('categorias.nombre', 'like', $search);
                });
            })
            ->when(!empty($query['categoria_id']), function ($builder) use ($query) {
                $builder->where('perfiles_grilla.categoria_id', $query['categoria_id']);
            })
            ->orderBy('perfiles_grilla.created_at')
            ->orderBy('perfiles_grilla.id')
            ->paginate($perPage)
            ->appends($query);

        return [
            'data' => $paginator->getCollection()->map(fn (PerfilGrilla $perfil) => $this->publicGrid($perfil))->values()->all(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ];
    }

  public function detail(int $id): array
{
    // Asegúrate de incluir la relación 'galeria' aquí
    $perfil = PerfilGrilla::with(['categoria:id,nombre', 'galeria'])->find($id);

    if (!$perfil) {
        return ['status' => 404, 'body' => ['message' => 'Perfil no encontrado']];
    }

    return ['status' => 200, 'body' => $this->publicDetail($perfil)];
}

    public function create(int $adminId, array $data): array
{
    $error = $this->validatePerfil($data);

    if ($error) {
        return ['status' => 400, 'body' => ['message' => $error]];
    }

    $perfil = DB::transaction(function () use ($adminId, $data) {
        // 1. Preparamos los datos
        $datosParaGuardar = $data;
        unset($datosParaGuardar['logo']); // Quitamos el objeto archivo

        // Procesamos el logo si existe
        if (isset($data['logo']) && $data['logo'] instanceof \Illuminate\Http\UploadedFile) {
            $ruta = $data['logo']->store('logos', 'directo_publico');
            $datosParaGuardar['logo'] = 'uploads/' . $ruta;
        }

        // Asignamos el autor
        $datosParaGuardar['creado_por'] = $adminId;

        // 2. Creamos el perfil
        $perfil = PerfilGrilla::create($datosParaGuardar);

        // 3. Procesamos la galería
        $this->replaceGallery($perfil, $data['galeria'] ?? []);

        // 4. Refrescamos para incluir relaciones en la respuesta
        $perfil->refresh();
        $perfil->load(['categoria:id,nombre', 'galeria']);

        return $perfil;
    });

    return ['status' => 201, 'body' => $this->publicDetail($perfil)];
}

public function update(int $id, array $data): array
{
    $perfil = PerfilGrilla::find($id);

    if (!$perfil) {
        return ['status' => 404, 'body' => ['message' => 'Perfil no encontrado']];
    }

    $error = $this->validatePerfil($data, true);
    if ($error) {
        return ['status' => 400, 'body' => ['message' => $error]];
    }

    $perfil = DB::transaction(function () use ($perfil, $data) {
        // 1. Preparamos los datos para la actualización
        $datosParaActualizar = $data;
        unset($datosParaActualizar['galeria']); // La galería se gestiona aparte

        // 2. Procesamos el logo si se envió uno nuevo
        if (isset($data['logo']) && $data['logo'] instanceof \Illuminate\Http\UploadedFile) {
            // Borrar el archivo viejo si existe
            if ($perfil->logo) {
                $rutaVieja = public_path($perfil->logo);
                if (\Illuminate\Support\Facades\File::exists($rutaVieja)) {
                    \Illuminate\Support\Facades\File::delete($rutaVieja);
                }
            }
            
            // Guardar el nuevo
            $rutaNueva = $data['logo']->store('logos', 'directo_publico');
            $datosParaActualizar['logo'] = 'uploads/' . $rutaNueva;
        } else {
            // Si no se envió logo, quitamos la clave para que no intente sobrescribir con null
            unset($datosParaActualizar['logo']);
        }

        // 3. Actualizamos el perfil usando el array filtrado
        $perfil->update($datosParaActualizar);

        // 4. Lógica de Galería
        if (array_key_exists('galeria', $data)) {
            $this->replaceGallery($perfil, $data['galeria']);
        }

        return $perfil->load(['categoria:id,nombre', 'galeria']);
    });

    return ['status' => 200, 'body' => $this->publicDetail($perfil)];
}

  // Dentro de PerfilService@delete
public function delete(int $id): array
{
    // 1. Usamos withTrashed() por si el registro ya fue marcado por SoftDelete
    $perfil = PerfilGrilla::withTrashed()->find($id);

    if (!$perfil) {
        return ['status' => 404, 'body' => ['message' => 'Perfil no encontrado']];
    }

    // 2. Borramos los archivos físicos (Logo)
    // Nota: Si el logo guarda una ruta absoluta o relativa, public_path() la encontrará.
    if ($perfil->logo) {
        $rutaLogo = public_path($perfil->logo);
        if (File::exists($rutaLogo)) {
            File::delete($rutaLogo);
        }
    }
    
    // 3. Borramos los archivos de la galería
    foreach ($perfil->galeria as $foto) {
        $rutaFoto = public_path($foto->imagen);
        if (File::exists($rutaFoto)) {
            File::delete($rutaFoto);
        }
    }

    // 4. Eliminación física real de la base de datos
    $perfil->forceDelete(); 

    return ['status' => 200, 'body' => ['message' => 'Perfil y archivos eliminados permanentemente']];
}

    public function softDelete(int $id): array
    {
        $perfil = PerfilGrilla::find($id);

        if (!$perfil) {
            return ['status' => 404, 'body' => ['message' => 'Perfil no encontrado']];
        }

        $perfil->delete();

        return ['status' => 200, 'body' => ['message' => 'Perfil dado de baja correctamente']];
    }

    public function restore(int $id): array
    {
        $perfil = PerfilGrilla::withTrashed()->find($id);

        if (!$perfil) {
            return ['status' => 404, 'body' => ['message' => 'Perfil no encontrado']];
        }

        if (!$perfil->trashed()) {
            return ['status' => 400, 'body' => ['message' => 'El perfil no está dado de baja']];
        }

        $perfil->restore();

        return ['status' => 200, 'body' => ['message' => 'Perfil restaurado correctamente']];
    }

    private function validatePerfil(array $data, bool $partial = false): ?string
    {
        if (!$partial && empty($data['nombre'])) {
            return 'El nombre es obligatorio';
        }

        if (!$partial && empty($data['categoria_id'])) {
            return 'La categoria_id es obligatoria';
        }

        if (!empty($data['categoria_id']) && !Categoria::find($data['categoria_id'])) {
            return 'La categoria no existe';
        }

        return null;
    }

private function replaceGallery(PerfilGrilla $perfil, array $galeria): void
{
    // 1. CARGA LAS FOTOS ANTES DE BORRAR NADA
    // Si no las guardamos en una variable, al hacer delete() en el paso 3, las perderás.
    $fotosViejas = $perfil->galeria; 

    // 2. BORRADO FÍSICO
    foreach ($fotosViejas as $foto) {
        // Obtenemos la ruta limpia (quitando cualquier URL si existiera)
        $rutaLimpia = str_replace(url('/'), '', $foto->imagen);
        $rutaLimpia = ltrim($rutaLimpia, '/');
        
        $rutaFisica = public_path($rutaLimpia);

        if (File::exists($rutaFisica)) {
            File::delete($rutaFisica);
        } else {
            Log::warning("No se encontró el archivo físico para borrar: " . $rutaFisica);
        }
    }

    // 3. AHORA SÍ: Borramos los registros de la base de datos
    $perfil->galeria()->delete();

    // 4. SUBIMOS LAS NUEVAS
    foreach ($galeria as $archivo) {
        if ($archivo instanceof \Illuminate\Http\UploadedFile) {
            $ruta = $archivo->store('galerias', 'directo_publico');
            $perfil->galeria()->create(['imagen' => 'uploads/' . $ruta]);
        }
    }
}

    private function publicGrid(PerfilGrilla $perfil): array
    {
        return [
            'id' => $perfil->id,
            'nombre' => $perfil->nombre,
            'descripcion' => $perfil->descripcion,
            'logo' => $perfil->logo, // <-- Cambiado
            'direccion' => $perfil->direccion,
            'experiencia' => $perfil->experiencia,
            'especializacion' => $perfil->especializacion,
            'contacto' => $perfil->contacto,
            'locales' => $perfil->locales,
            'link' => $perfil->link,
            'categoria_id' => $perfil->categoria_id,
            'categoria_nombre' => $perfil->categoria?->nombre,
            'galeria' => $perfil->galeria->map(fn ($imagen) => [
            'id' => $imagen->id,
            'imagen' => $imagen->imagen, // El accesor hará que salga la URL completa
        ]),
            'created_at' => $perfil->created_at,
        ];
    }

    private function publicDetail(PerfilGrilla $perfil): array
    {
        return [
            ...$this->publicGrid($perfil),
            'creado_por' => $perfil->creado_por,
            'galeria' => $perfil->galeria->map(fn ($imagen) => [
                'id' => $imagen->id,
                'imagen' => $imagen->imagen, // <-- Cambiado
            ]),
        ];
    }
}