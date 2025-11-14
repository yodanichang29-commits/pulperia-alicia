<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'MAMI',
                'email'    => 'mami@pulperia.com',
                'password' => Hash::make('mami123'),
            ],
            [
                'name'     => 'PAPI',
                'email'    => 'papi@pulperia.com',
                'password' => Hash::make('papi123'),
            ],
            [
                'name'     => 'NATALY',
                'email'    => 'nataly@pulperia.com',
                'password' => Hash::make('nataly123'),
            ],
            [
                'name'     => 'OTROS',
                'email'    => 'otros@pulperia.com',
                'password' => Hash::make('otros123'),
            ],
        ];

        foreach ($users as $data) {
            // Si ya existe un usuario con ese name, no lo duplica
            User::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}
