<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    protected $fillable = ['codigo', 'cliente', 'mesa', 'fecha', 'estado', 'total', 'notas'];

    protected $casts = [
        'fecha' => 'datetime',
        'mesa' => 'integer',
        'total' => 'decimal:2',
    ];

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class);
    }

    public function platillos(): BelongsToMany
    {
        return $this->belongsToMany(Platillo::class, 'detalle_pedidos')
            ->withPivot('cantidad', 'precio_unitario', 'subtotal')
            ->withTimestamps();
    }
}
