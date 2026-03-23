<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progreso_estudiantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('tema_id')->constrained('temas')->onDelete('cascade');
            $table->enum('estado', ['bloqueado', 'disponible', 'en_progreso', 'completado'])->default('bloqueado');
            $table->integer('porcentaje_lectura')->default(0);
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_completado')->nullable();
            $table->timestamps();

            $table->unique(['estudiante_id', 'tema_id']);
            $table->index('estado');
            $table->index('estudiante_id');
            $table->index('tema_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progreso_estudiantes');
    }
};
