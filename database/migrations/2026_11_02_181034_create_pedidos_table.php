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
        // [MIGRACION] - Creación de la tabla de pedidos del restaurante
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id(); // [MIGRACION] Llave primaria autoincremental
            $table->string('codigo', 20)->unique(); // Código único de pedido (ej. PED-2026-001)
            $table->string('cliente', 100); // Nombre o identificación del cliente
            $table->unsignedSmallInteger('mesa'); // Número de mesa
            $table->dateTime('fecha'); // Fecha y hora del pedido
            $table->enum('estado', ['pendiente', 'en_preparacion', 'servido', 'pagado', 'cancelado'])->default('pendiente');
            $table->decimal('total', 10, 2)->default(0.00); // Total calculado en servidor
            $table->text('notas')->nullable(); // Indicaciones especiales o alergias
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
