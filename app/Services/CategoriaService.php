<?php

namespace App\Services;

use App\Models\Categoria;

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

    // Eliminacion protegida de categorias.
    public function delete(int $id): array
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return ['status' => 404, 'body' => ['message' => 'Categoria no encontrada']];
        }

        try {
            $categoria->delete();
            return ['status' => 200, 'body' => ['message' => 'Categoria eliminada correctamente']];
        } catch (\Throwable) {
            return ['status' => 409, 'body' => ['message' => 'No se puede eliminar una categoria con perfiles asociados']];
        }
    }

    // Baja logica de categoria.
    public function softDelete(int $id): array
    {
        $categoria = Categoria::find($id);

        if (!$categoria) {
            return ['status' => 404, 'body' => ['message' => 'Categoria no encontrada']];
        }

        $categoria->delete();

        return ['status' => 200, 'body' => ['message' => 'Categoria dada de baja correctamente']];
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
}
