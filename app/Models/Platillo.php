<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Platillo extends Model
{
    use HasFactory;

    // [ENCAPSULAMIENTO] Campos permitidos para asignacion masiva.
    protected $fillable = [
        'categoria_id', 'nombre', 'descripcion', 'precio',
        'tiempo_preparacion', 'disponible', 'imagen',
    ];

    // [ENCAPSULAMIENTO] Conversion de atributos a sus tipos de datos.
    protected $casts = [
        'precio' => 'decimal:2',
        'tiempo_preparacion' => 'integer',
        'disponible' => 'boolean',
    ];

    // [RELACION] Asociacion Eloquent con las entidades del restaurante.
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    // [RELACION] Asociacion Eloquent con las entidades del restaurante.
    public function detallePedidos(): HasMany
    {
        return $this->hasMany(DetallePedido::class);
    }

    // [RELACION] Asociacion Eloquent con las entidades del restaurante.
    public function pedidos(): BelongsToMany
    {
        return $this->belongsToMany(Pedido::class, 'detalle_pedidos')
            ->withPivot('cantidad', 'precio_unitario', 'subtotal')
            ->withTimestamps();
    }
}
