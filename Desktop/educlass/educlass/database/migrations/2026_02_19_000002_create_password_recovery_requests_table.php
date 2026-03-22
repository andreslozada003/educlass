<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('password_recovery_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('estudiante_id')->nullable();
            $table->string('nombre_estudiante');
            $table->string('nombre_normalizado');
            $table->unsignedBigInteger('docente_id')->nullable();
            $table->enum('estado', ['pendiente', 'atendida', 'rechazada'])->default('pendiente');
            $table->text('mensaje_docente')->nullable();
            $table->timestamp('solicitado_en')->nullable();
            $table->timestamp('respondido_en')->nullable();
            $table->timestamps();

            $table->foreign('estudiante_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('docente_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['nombre_normalizado', 'created_at']);
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_recovery_requests');
    }
};

