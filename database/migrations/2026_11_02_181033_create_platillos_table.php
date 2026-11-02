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
        // [MIGRACION] - Creación de la tabla principal de platillos del menú
        Schema::create('platillos', function (Blueprint $table) {
            $table->id(); // [MIGRACION] Llave primaria autoincremental
            $table->foreignId('categoria_id')
                ->constrained('categorias')
                ->restrictOnDelete(); // [RELACION] Llave foránea hacia categorías (protege contra eliminación accidental)
            $table->string('nombre', 100)->unique(); // Nombre de platillo único
            $table->text('descripcion');
            $table->decimal('precio', 8, 2); // Precio unitario (mayor a 0)
            $table->unsignedSmallInteger('tiempo_preparacion'); // Tiempo en minutos (1 a 180 min)
            $table->boolean('disponible')->default(true); // Disponibilidad en menú
            $table->string('imagen')->nullable(); // Ruta relativa en disco público (storage/app/public/platillos)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platillos');
    }
};
