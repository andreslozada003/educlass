<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('asignatura_id')->nullable()->constrained('asignaturas')->onDelete('cascade');
            $table->enum('categoria', ['juegos', 'evaluaciones', 'temas', 'general']);
            $table->integer('posicion')->default(0);
            $table->integer('puntaje_total')->default(0);
            $table->integer('nivel_alcanzado')->default(1);
            $table->timestamp('fecha_actualizacion')->useCurrent();
            $table->timestamps();

            $table->unique(['estudiante_id', 'asignatura_id', 'categoria']);
            $table->index(['categoria', 'posicion']);
            $table->index('puntaje_total');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rankings');
    }
};
