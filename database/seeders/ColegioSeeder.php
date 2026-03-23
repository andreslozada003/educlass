<?php

namespace Database\Seeders;

use App\Models\Colegio;
use Illuminate\Database\Seeder;

class ColegioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colegios = [
            [
                'nombre' => 'Instituto Educativo Nacional',
                'direccion' => 'Calle Principal 123, Ciudad',
                'telefono' => '555-0101',
                'activo' => true,
            ],
            [
                'nombre' => 'Colegio San José',
                'direccion' => 'Avenida Central 456, Ciudad',
                'telefono' => '555-0102',
                'activo' => true,
            ],
            [
                'nombre' => 'Liceo Pedagógico',
                'direccion' => 'Carrera 7 # 89-10, Ciudad',
                'telefono' => '555-0103',
                'activo' => true,
            ],
        ];

        foreach ($colegios as $colegio) {
            Colegio::create($colegio);
        }
    }
}
