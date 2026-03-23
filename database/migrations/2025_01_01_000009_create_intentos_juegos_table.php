<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intentos_juegos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('juego_id')->constrained('juegos')->onDelete('cascade');
            $table->integer('puntaje_obtenido')->default(0);
            $table->json('respuestas')->nullable();
            $table->integer('duracion_segundos')->default(0);
            $table->integer('numero_intento')->default(1);
            $table->boolean('completado')->default(false);
            $table->timestamp('fecha_intento')->useCurrent();
            $table->timestamps();

            $table->index(['estudiante_id', 'juego_id']);
            $table->index('completado');
            $table->index('fecha_intento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intentos_juegos');
    }
};
