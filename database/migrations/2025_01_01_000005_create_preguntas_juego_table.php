<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preguntas_juego', function (Blueprint $table) {
            $table->id();
            $table->foreignId('juego_id')->constrained('juegos')->onDelete('cascade');
            $table->enum('tipo', ['opcion_multiple', 'verdadero_falso', 'emparejamiento', 'ordenamiento']);
            $table->text('enunciado');
            $table->json('opciones')->nullable();
            $table->json('respuesta_correcta');
            $table->integer('puntaje')->default(10);
            $table->integer('orden')->default(0);
            $table->string('imagen_apoyo', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('juego_id');
            $table->index('orden');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preguntas_juego');
    }
};
