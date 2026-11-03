<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    // [ENCAPSULAMIENTO] Campos permitidos para asignacion masiva.
    protected $fillable = ['codigo', 'cliente', 'mesa', 'fecha', 'estado', 'total', 'notas'];

    // [ENCAPSULAMIENTO] Conversion de atributos a sus tipos de datos.
    protected $casts = [
        'fecha' => 'datetime',
        'mesa' => 'integer',
        'total' => 'decimal:2',
    ];

    // [RELACION] Asociacion Eloquent con las entidades del restaurante.
    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class);
    }

    // [RELACION] Asociacion Eloquent con las entidades del restaurante.
    public function platillos(): BelongsToMany
    {
        return $this->belongsToMany(Platillo::class, 'detalle_pedidos')
            ->withPivot('cantidad', 'precio_unitario', 'subtotal')
            ->withTimestamps();
    }
}
