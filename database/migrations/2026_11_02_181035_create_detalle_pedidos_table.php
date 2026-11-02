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
        // [MIGRACION] - Creación de la tabla intermedia detalle_pedidos (Relación N:M con datos adicionales)
        Schema::create('detalle_pedidos', function (Blueprint $table) {
            $table->id(); // [MIGRACION] Llave primaria autoincremental
            $table->foreignId('pedido_id')
                ->constrained('pedidos')
                ->cascadeOnDelete(); // [RELACION] Si se elimina un pedido, se eliminan sus líneas de detalle
            $table->foreignId('platillo_id')
                ->constrained('platillos')
                ->restrictOnDelete(); // [RELACION] Impide eliminar un platillo histórico que ya forma parte de un pedido
            $table->unsignedSmallInteger('cantidad'); // Cantidad solicitada (mayor a 0)
            $table->decimal('precio_unitario', 8, 2); // Precio congelado al momento del pedido
            $table->decimal('subtotal', 10, 2); // Subtotal calculado en el servidor (cantidad * precio_unitario)
            $table->timestamps();

            // Garantiza que un platillo no se duplique en renglones separados para el mismo pedido
            $table->unique(['pedido_id', 'platillo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_pedidos');
    }
};
