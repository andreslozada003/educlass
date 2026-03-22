<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logros_estudiantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('logro_id')->constrained('logros')->onDelete('cascade');
            $table->timestamp('fecha_obtenido')->useCurrent();
            $table->json('contexto')->nullable();
            $table->timestamps();

            $table->unique(['estudiante_id', 'logro_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logros_estudiantes');
    }
};
