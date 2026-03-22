<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('temas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignatura_id')->constrained('asignaturas')->onDelete('cascade');
            $table->string('titulo', 200);
            $table->string('slug', 200)->unique();
            $table->longText('contenido');
            $table->integer('dificultad')->default(1)->comment('1-4: Básico a Experto');
            $table->integer('periodo_academico')->default(1)->comment('1-3');
            $table->integer('orden')->default(0);
            $table->string('imagen_destacada', 255)->nullable();
            $table->string('video_url', 500)->nullable();
            $table->integer('tiempo_estimado_minutos')->default(15);
            $table->boolean('activo')->default(true);
            $table->foreignId('docente_creador_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index('asignatura_id');
            $table->index('dificultad');
            $table->index('periodo_academico');
            $table->index('orden');
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('temas');
    }
};
