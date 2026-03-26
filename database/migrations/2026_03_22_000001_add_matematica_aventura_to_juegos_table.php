<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("
                ALTER TABLE juegos
                MODIFY COLUMN tipo ENUM(
                    'quiz',
                    'memoria',
                    'arrastrar',
                    'completar',
                    'ordenar',
                    'sopa',
                    'clasificar',
                    'matematica_aventura'
                ) NOT NULL
            ");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::table('juegos')
                ->where('tipo', 'matematica_aventura')
                ->update(['tipo' => 'quiz']);

            DB::statement("
                ALTER TABLE juegos
                MODIFY COLUMN tipo ENUM(
                    'quiz',
                    'memoria',
                    'arrastrar',
                    'completar',
                    'ordenar',
                    'sopa',
                    'clasificar'
                ) NOT NULL
            ");
        }
    }
};
