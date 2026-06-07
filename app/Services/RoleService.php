<?php

namespace App\Services;

use App\Models\Role;

class RoleService
{
    // Listado administrativo de roles.
    public function list()
    {
        return Role::orderBy('id')->get();
    }

    // Creacion de roles para escalar permisos.
    public function create(array $data): array
    {
        if (empty($data['nombre'])) {
            return ['status' => 400, 'body' => ['message' => 'El nombre es obligatorio']];
        }

        try {
            return ['status' => 201, 'body' => Role::create(['nombre' => $data['nombre']])];
        } catch (\Throwable) {
            return ['status' => 409, 'body' => ['message' => 'El rol ya existe']];
        }
    }

    // Edicion de roles existentes.
    public function update(int $id, array $data): array
    {
        if (empty($data['nombre'])) {
            return ['status' => 400, 'body' => ['message' => 'El nombre es obligatorio']];
        }

        $role = Role::find($id);

        if (!$role) {
            return ['status' => 404, 'body' => ['message' => 'Rol no encontrado']];
        }

        try {
            $role->update(['nombre' => $data['nombre']]);
            return ['status' => 200, 'body' => $role];
        } catch (\Throwable) {
            return ['status' => 409, 'body' => ['message' => 'El rol ya existe']];
        }
    }

    // Eliminacion de roles no asociados.
    public function delete(int $id): array
    {
        $role = Role::find($id);

        if (!$role) {
            return ['status' => 404, 'body' => ['message' => 'Rol no encontrado']];
        }

        try {
            $role->delete();
            return ['status' => 200, 'body' => ['message' => 'Rol eliminado correctamente']];
        } catch (\Throwable) {
            return ['status' => 409, 'body' => ['message' => 'No se puede eliminar un rol con usuarios asociados']];
        }
    }
}
