<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetallePedido extends Model
{
    // [ENCAPSULAMIENTO] Campos permitidos para asignacion masiva.
    protected $fillable = ['pedido_id', 'platillo_id', 'cantidad', 'precio_unitario', 'subtotal'];

    // [ENCAPSULAMIENTO] Conversion de atributos a sus tipos de datos.
    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // [RELACION] Asociacion Eloquent con las entidades del restaurante.
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    // [RELACION] Asociacion Eloquent con las entidades del restaurante.
    public function platillo(): BelongsTo
    {
        return $this->belongsTo(Platillo::class);
    }
}
