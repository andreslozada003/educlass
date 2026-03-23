<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preguntas_evaluacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluacion_id')->constrained('evaluaciones')->onDelete('cascade');
            $table->enum('tipo', ['multiple', 'vf', 'corta', 'emparejamiento']);
            $table->text('enunciado');
            $table->json('opciones')->nullable();
            $table->text('respuesta_correcta');
            $table->integer('puntaje')->default(10);
            $table->integer('orden')->default(0);
            $table->string('imagen_apoyo', 255)->nullable();
            $table->timestamps();

            $table->index('evaluacion_id');
            $table->index('tipo');
            $table->index('orden');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preguntas_evaluacion');
    }
};
