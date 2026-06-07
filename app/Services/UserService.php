<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    // Listado administrativo de usuarios.
    public function list(): array
    {
        return User::with('role')
            ->orderBy('id')
            ->get()
            ->map(fn (User $user) => $this->publicUser($user))
            ->all();
    }

    // Creacion administrativa de usuarios.
    public function create(array $data): array
    {
        if (empty($data['user']) || empty($data['password']) || empty($data['rol_id'])) {
            return ['status' => 400, 'body' => ['message' => 'User, password y rol_id son obligatorios']];
        }

        if (!Role::find($data['rol_id'])) {
            return ['status' => 400, 'body' => ['message' => 'El rol no existe']];
        }

        try {
            $user = User::create([
                'user' => $data['user'],
                'password' => Hash::make($data['password']),
                'rol_id' => $data['rol_id'],
            ])->load('role');

            return ['status' => 201, 'body' => $this->publicUser($user)];
        } catch (\Throwable) {
            return ['status' => 409, 'body' => ['message' => 'El user ya existe']];
        }
    }

    // Edicion administrativa de usuarios.
    public function update(int $id, array $data): array
    {
        $user = User::find($id);

        if (!$user) {
            return ['status' => 404, 'body' => ['message' => 'Usuario no encontrado']];
        }

        if (!empty($data['rol_id']) && !Role::find($data['rol_id'])) {
            return ['status' => 400, 'body' => ['message' => 'El rol no existe']];
        }

        try {
            $user->update([
                'user' => $data['user'] ?? $user->user,
                'password' => !empty($data['password']) ? Hash::make($data['password']) : $user->password,
                'rol_id' => $data['rol_id'] ?? $user->rol_id,
            ]);

            return ['status' => 200, 'body' => $this->publicUser($user->load('role'))];
        } catch (\Throwable) {
            return ['status' => 409, 'body' => ['message' => 'El user ya existe']];
        }
    }

    // Eliminacion administrativa de usuarios.
    public function delete(int $id, int $adminId): array
    {
        if ($id === $adminId) {
            return ['status' => 400, 'body' => ['message' => 'No puedes eliminar tu propio usuario']];
        }

        $user = User::find($id);

        if (!$user) {
            return ['status' => 404, 'body' => ['message' => 'Usuario no encontrado']];
        }

        try {
            $user->delete();
            return ['status' => 200, 'body' => ['message' => 'Usuario eliminado correctamente']];
        } catch (\Throwable) {
            return ['status' => 409, 'body' => ['message' => 'No se puede eliminar un usuario con perfiles asociados']];
        }
    }

    private function publicUser(User $user): array
    {
        return [
            'id' => $user->id,
            'user' => $user->user,
            'rol_id' => $user->rol_id,
            'role' => $user->role?->nombre,
        ];
    }
}
