<?php

namespace App\Services;

use App\Models\Categoria;
use App\Models\PerfilGrilla;
use Illuminate\Support\Facades\DB;

class PerfilService
{
    // Listado publico para grilla con busqueda por nombre, descripcion o categoria.
   public function list(array $query)
{
    $perPage = min(max((int) ($query['per_page'] ?? 12), 1), 50);

    $paginator = PerfilGrilla::query()
        ->select('perfiles_grilla.*')
        ->with('categoria:id,nombre')
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

    // Detalle publico para modal.
    public function detail(int $id): array
    {
        $perfil = PerfilGrilla::with(['categoria:id,nombre', 'galeria:id,perfil_id,imagen_base64'])->find($id);

        if (!$perfil) {
            return ['status' => 404, 'body' => ['message' => 'Perfil no encontrado']];
        }

        return ['status' => 200, 'body' => $this->publicDetail($perfil)];
    }

    // Creacion protegida de perfiles y galeria.
    public function create(int $adminId, array $data): array
    {
        $error = $this->validatePerfil($data);

        if ($error) {
            return ['status' => 400, 'body' => ['message' => $error]];
        }

        $perfil = DB::transaction(function () use ($adminId, $data) {
            $perfil = PerfilGrilla::create([
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'] ?? null,
                'logo_base64' => $data['logo_base64'] ?? null,
                'categoria_id' => $data['categoria_id'],
                'creado_por' => $adminId,
                'direccion' => $data['direccion'] ?? null,
                'experiencia' => $data['experiencia'] ?? null,
                'especializacion' => $data['especializacion'] ?? null,
                'contacto' => $data['contacto'] ?? null,
                'locales' => $data['locales'] ?? null,
            ]);

            $this->replaceGallery($perfil, $data['galeria'] ?? []);

            return $perfil->load(['categoria:id,nombre', 'galeria:id,perfil_id,imagen_base64']);
        });

        return ['status' => 201, 'body' => $this->publicDetail($perfil)];
    }

    // Edicion protegida de perfiles y reemplazo opcional de galeria.
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
            $perfil->update([
                'nombre' => $data['nombre'] ?? $perfil->nombre,
                'descripcion' => array_key_exists('descripcion', $data) ? $data['descripcion'] : $perfil->descripcion,
                'logo_base64' => array_key_exists('logo_base64', $data) ? $data['logo_base64'] : $perfil->logo_base64,
                'categoria_id' => $data['categoria_id'] ?? $perfil->categoria_id,
                'direccion' => array_key_exists('direccion', $data) ? $data['direccion'] : $perfil->direccion,
                'experiencia' => array_key_exists('experiencia', $data) ? $data['experiencia'] : $perfil->experiencia,
                'especializacion' => array_key_exists('especializacion', $data) ? $data['especializacion'] : $perfil->especializacion,
                'contacto' => array_key_exists('contacto', $data) ? $data['contacto'] : $perfil->contacto,
                'locales' => array_key_exists('locales', $data) ? $data['locales'] : $perfil->locales,
            ]);

            if (array_key_exists('galeria', $data)) {
                $this->replaceGallery($perfil, $data['galeria'] ?? []);
            }

            return $perfil->load(['categoria:id,nombre', 'galeria:id,perfil_id,imagen_base64']);
        });

        return ['status' => 200, 'body' => $this->publicDetail($perfil)];
    }

    // Eliminacion protegida de perfiles.
    public function delete(int $id): array
    {
        $perfil = PerfilGrilla::find($id);

        if (!$perfil) {
            return ['status' => 404, 'body' => ['message' => 'Perfil no encontrado']];
        }

        $perfil->delete();

        return ['status' => 200, 'body' => ['message' => 'Perfil eliminado correctamente']];
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
        $perfil->galeria()->delete();

        foreach ($galeria as $imagen) {
            $imagenBase64 = is_array($imagen) ? ($imagen['imagen_base64'] ?? null) : $imagen;

            if ($imagenBase64) {
                $perfil->galeria()->create(['imagen_base64' => $imagenBase64]);
            }
        }
    }

    private function publicGrid(PerfilGrilla $perfil): array
    {
        return [
            'id' => $perfil->id,
            'nombre' => $perfil->nombre,
            'descripcion' => $perfil->descripcion,
            'logo_base64' => $perfil->logo_base64,
            'direccion' => $perfil->direccion,
            'experiencia' => $perfil->experiencia,
            'especializacion' => $perfil->especializacion,
            'contacto' => $perfil->contacto,
            'locales' => $perfil->locales,
            'categoria_id' => $perfil->categoria_id,
            'categoria_nombre' => $perfil->categoria?->nombre,
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
                'imagen_base64' => $imagen->imagen_base64,
            ]),
        ];
    }
}
