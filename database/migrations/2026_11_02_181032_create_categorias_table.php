<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // [MIGRACION] - Creación de la tabla catálogo de categorías para platillos
        Schema::create('categorias', function (Blueprint $table) {
            $table->id(); // [MIGRACION] Llave primaria autoincremental
            $table->string('nombre', 60)->unique();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
