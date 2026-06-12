<?php

namespace App\Services;

use App\Models\Categoria;
use App\Models\PerfilGrilla; // <-- IMPORTANTE: Importar el modelo de perfiles para el conteo

class CategoriaService
{
    // Listado publico de categorias con paginacion.
    public function list(array $query = []): array
    {
        $perPage = min(max((int) ($query['per_page'] ?? 12), 1), 50);

        $paginator = Categoria::withCount('perfiles')
            ->orderBy('nombre')
            ->paginate($perPage)
            ->appends($query);

        return [
            'data' => $paginator->getCollection()->map(fn (Categoria $categoria) => [
                'id' => $categoria->id,
                'nombre' => $categoria->nombre,
                'perfiles_count' => $categoria->perfiles_count,
            ])->values()->all(),
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

    // Creacion protegida de categorias.
    public function create(array $data): array
    {
        if (empty($data['nombre'])) {
            return ['status' => 400, 'body' => ['message' => 'El nombre es obligatorio']];
        }

        try {
            return ['status' => 201, 'body' => Categoria::create(['nombre' => $data['nombre']])];
        } catch (\Throwable) {
            return ['status' => 409, 'body' => ['message' => 'La categoria ya existe']];
        }
    }

    // Edicion protegida de categorias.
    public function update(int $id, array $data): array
    {
        if (empty($data['nombre'])) {
            return ['status' => 400, 'body' => ['message' => 'El nombre es obligatorio']];
        }

        $categoria = Categoria::find($id);

        if (!$categoria) {
            return ['status' => 404, 'body' => ['message' => 'Categoria no encontrada']];
        }

        try {
            $categoria->update(['nombre' => $data['nombre']]);
            return ['status' => 200, 'body' => $categoria];
        } catch (\Throwable) {
            return ['status' => 409, 'body' => ['message' => 'La categoria ya existe']];
        }
    }

    // Eliminacion FISICA y permanente de categorias.
    public function delete(int $id): array
    {
        // Buscamos con withTrashed() por si quieren eliminar físicamente una que ya estaba en papelera
        $categoria = Categoria::withTrashed()->find($id);

        if (!$categoria) {
            return ['status' => 404, 'body' => ['message' => 'Categoria no encontrada']];
        }

        // 1. BLINDAJE: Validar si tiene perfiles asociados
        $errorValidacion = $this->verificarPerfilesAsociados($id);
        if ($errorValidacion) {
            return $errorValidacion;
        }

        try {
            // Como usarás SoftDeletes, para borrar definitivo de la DB se usa forceDelete()
            $categoria->forceDelete();
            return ['status' => 200, 'body' => ['message' => 'Categoria eliminada permanentemente de la base de datos']];
        } catch (\Throwable) {
            return ['status' => 409, 'body' => ['message' => 'No se pudo procesar la eliminación destructiva de la categoria']];
        }
    }

    // Baja logica de categoria (Soft Delete).
    public function softDelete(int $id): array
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return ['status' => 404, 'body' => ['message' => 'Categoria no encontrada o ya archivada']];
        }

        // 1. BLINDAJE: Validar si tiene perfiles asociados
        $errorValidacion = $this->verificarPerfilesAsociados($id);
        if ($errorValidacion) {
            return $errorValidacion;
        }

        $categoria->delete(); // Guarda la fecha en deleted_at

        return ['status' => 200, 'body' => ['message' => 'Categoria dada de baja y enviada a la papelera correctamente']];
    }

    // Restauracion de categoria desactivada.
    public function restore(int $id): array
    {
        $categoria = Categoria::withTrashed()->find($id);

        if (!$categoria) {
            return ['status' => 404, 'body' => ['message' => 'Categoria no encontrada']];
        }

        if (!$categoria->trashed()) {
            return ['status' => 400, 'body' => ['message' => 'La categoria no está dada de baja']];
        }

        $categoria->restore();

        return ['status' => 200, 'body' => ['message' => 'Categoria restaurada correctamente']];
    }

    // Detalle publico de categoria.
    public function detail(int $id): array
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return ['status' => 404, 'body' => ['message' => 'Categoria no encontrada']];
        }

        return ['status' => 200, 'body' => $categoria];
    }

    // Busqueda publica por nombre (autocomplete/simple search).
    public function search(string $term = ''): array
    {
        $term = trim($term);

        if ($term === '') {
            return ['status' => 200, 'body' => []];
        }

        $results = Categoria::where('nombre', 'like', "%{$term}%")
            ->orderBy('nombre')
            ->limit(20)
            ->get()
            ->map(fn (Categoria $c) => [
                'id' => $c->id,
                'nombre' => $c->nombre,
            ])->values()->all();

        return ['status' => 200, 'body' => $results];
    }

    /**
     * Valida si existen perfiles activos o archivados vinculados a esta categoría.
     */
    private function verificarPerfilesAsociados(int $categoriaId): ?array
    {
        // Contamos perfiles normales y los que están en la papelera (withTrashed)
        $conteo = PerfilGrilla::withTrashed()
            ->where('categoria_id', $categoriaId)
            ->count();

        if ($conteo > 0) {
            return [
                'status' => 400, // Bad Request controladísimo para tu frontend React
                'body' => [
                    'message' => "No se puede eliminar la categoría porque tiene {$conteo} perfil(es) asociado(s) (incluyendo en papelera)."
                ]
            ];
        }

        return null; // Todo limpio, puede proceder con el borrado
    }
}