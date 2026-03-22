<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tema_id')->constrained('temas')->onDelete('cascade');
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['diagnostica', 'formativa', 'sumativa'])->default('formativa');
            $table->integer('tiempo_limite_minutos')->default(30);
            $table->integer('puntaje_total')->default(100);
            $table->integer('intentos_permitidos')->default(3);
            $table->integer('umbral_aprobacion')->default(60);
            $table->boolean('activa')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('tema_id');
            $table->index('tipo');
            $table->index('activa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluaciones');
    }
};
