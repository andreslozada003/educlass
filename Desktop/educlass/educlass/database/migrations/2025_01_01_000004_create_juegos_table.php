<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('juegos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tema_id')->constrained('temas')->onDelete('cascade');
            $table->enum('tipo', ['quiz', 'memoria', 'arrastrar', 'completar', 'ordenar', 'sopa', 'clasificar']);
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->json('configuracion')->nullable();
            $table->integer('dificultad')->default(1);
            $table->integer('intentos_maximos')->default(5);
            $table->integer('puntaje_base')->default(100);
            $table->integer('tiempo_limite_segundos')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('tema_id');
            $table->index('tipo');
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('juegos');
    }
};
