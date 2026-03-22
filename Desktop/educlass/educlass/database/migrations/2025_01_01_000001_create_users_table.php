<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['estudiante', 'docente', 'admin'])->default('estudiante');
            $table->string('nombre', 150);
            $table->foreignId('colegio_id')->nullable()->constrained('colegios')->onDelete('set null');
            $table->string('email', 150)->unique();
            $table->string('telefono', 20)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255);
            $table->string('remember_token', 100)->nullable();
            $table->string('reset_token', 100)->nullable();
            $table->timestamp('reset_expira')->nullable();
            $table->string('avatar', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('ultimo_acceso')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('tipo');
            $table->index('colegio_id');
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
