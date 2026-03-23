<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resultados_evaluaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('evaluacion_id')->constrained('evaluaciones')->onDelete('cascade');
            $table->integer('puntaje_obtenido')->default(0);
            $table->json('respuestas')->nullable();
            $table->integer('tiempo_empleado_minutos')->default(0);
            $table->boolean('aprobado')->default(false);
            $table->timestamp('fecha_realizacion')->useCurrent();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['estudiante_id', 'evaluacion_id']);
            $table->index('aprobado');
            $table->index('fecha_realizacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resultados_evaluaciones');
    }
};
