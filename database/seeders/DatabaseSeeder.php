<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Roles iniciales del sistema.
        $adminRole = Role::firstOrCreate(['nombre' => 'admin']);
        $userRole = Role::firstOrCreate(['nombre' => 'user']);

        // Usuarios iniciales sin duplicar.
        User::updateOrCreate(['user' => 'admin'], [
            'password' => Hash::make('admin123'),
            'rol_id' => $adminRole->id,
        ]);

        User::updateOrCreate(['user' => 'user'], [
            'password' => Hash::make('user123'),
            'rol_id' => $userRole->id,
        ]);

        // Categorias iniciales para la grilla publica.
        foreach (['Abogados', 'Contadores', 'Arquitectos', 'Ingenieros'] as $nombre) {
            Categoria::firstOrCreate(['nombre' => $nombre]);
        }
    }
}
