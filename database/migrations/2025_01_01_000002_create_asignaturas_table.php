<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaturas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->string('slug', 100)->unique();
            $table->string('icono', 50)->nullable();
            $table->string('color_primario', 7)->default('#3B82F6');
            $table->string('color_secundario', 7)->default('#60A5FA');
            $table->integer('orden')->default(0);
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->index('orden');
            $table->index('activa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaturas');
    }
};
