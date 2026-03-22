<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Colegio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colegio = Colegio::first();

        // Crear docentes
        $docentes = [
            [
                'tipo' => 'docente',
                'nombre' => 'Prof. María González',
                'email' => 'docente1@educlass.com',
                'colegio_id' => $colegio->id,
                'password' => Hash::make('password123'),
                'activo' => true,
            ],
            [
                'tipo' => 'docente',
                'nombre' => 'Prof. Carlos Rodríguez',
                'email' => 'docente2@educlass.com',
                'colegio_id' => $colegio->id,
                'password' => Hash::make('password123'),
                'activo' => true,
            ],
        ];

        foreach ($docentes as $docente) {
            User::create($docente);
        }

        // Crear estudiantes de prueba
        $estudiantes = [
            ['nombre' => 'Ana Martínez', 'email' => 'ana@demo.com'],
            ['nombre' => 'Luis Hernández', 'email' => 'luis@demo.com'],
            ['nombre' => 'Sofía López', 'email' => 'sofia@demo.com'],
            ['nombre' => 'Diego García', 'email' => 'diego@demo.com'],
            ['nombre' => 'Valentina Torres', 'email' => 'valentina@demo.com'],
            ['nombre' => 'Mateo Ramírez', 'email' => 'mateo@demo.com'],
            ['nombre' => 'Camila Flores', 'email' => 'camila@demo.com'],
            ['nombre' => 'Daniel Cruz', 'email' => 'daniel@demo.com'],
            ['nombre' => 'Isabella Reyes', 'email' => 'isabella@demo.com'],
            ['nombre' => 'Alejandro Morales', 'email' => 'alejandro@demo.com'],
        ];

        foreach ($estudiantes as $estudiante) {
            User::create([
                'tipo' => 'estudiante',
                'nombre' => $estudiante['nombre'],
                'email' => $estudiante['email'],
                'colegio_id' => $colegio->id,
                'password' => Hash::make('password123'),
                'activo' => true,
            ]);
        }

        // Crear admin
        User::create([
            'tipo' => 'admin',
            'nombre' => 'Administrador',
            'email' => 'admin@educlass.com',
            'colegio_id' => null,
            'password' => Hash::make('admin123'),
            'activo' => true,
        ]);
    }
}
