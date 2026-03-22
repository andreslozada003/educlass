<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ColegioSeeder::class,
            AsignaturaSeeder::class,
            UserSeeder::class,
            ConfiguracionSeeder::class,
            LogroSeeder::class,
            // Los siguientes seeders crean contenido demo
            // Comentar si no se desea contenido de prueba
            TemasSeeder::class,
            JuegosSeeder::class,
            EvaluacionesSeeder::class,
        ]);
    }
}
