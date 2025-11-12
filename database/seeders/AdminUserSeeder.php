<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Contraseña compartida para todos los usuarios familiares
        $sharedPassword = Hash::make('bellacrosh2001');

        // Crear los 4 usuarios familiares
        $usuarios = [
            ['name' => 'MAMI', 'email' => 'mami@pulperia.com'],
            ['name' => 'PAPI', 'email' => 'papi@pulperia.com'],
            ['name' => 'NATALY', 'email' => 'nataly@pulperia.com'],
            ['name' => 'OTROS', 'email' => 'otros@pulperia.com'],
        ];

        foreach ($usuarios as $usuario) {
            User::updateOrCreate(
                ['email' => $usuario['email']],
                [
                    'name' => $usuario['name'],
                    'password' => $sharedPassword
                ]
            );
        }
    }
}
