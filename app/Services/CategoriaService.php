<?php

namespace App\Services;

use App\Models\Categoria;

class CategoriaService
{
    // Listado publico de categorias.
    public function list()
    {
        return Categoria::orderBy('nombre')->get();
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
}
