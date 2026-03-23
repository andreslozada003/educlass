<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calificaciones_periodo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('asignatura_id')->constrained('asignaturas')->onDelete('cascade');
            $table->integer('periodo')->default(1)->comment('1-3');
            $table->decimal('promedio_juegos', 5, 2)->default(0);
            $table->decimal('promedio_evaluaciones', 5, 2)->default(0);
            $table->decimal('promedio_ponderado', 5, 2)->default(0);
            $table->integer('año_academico')->default(2025);
            $table->timestamps();

            $table->unique(['estudiante_id','asignatura_id','periodo','año_academico'],
            'calif_periodo_unique'
);
            $table->index('periodo');
            $table->index('año_academico');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calificaciones_periodo');
    }
};
