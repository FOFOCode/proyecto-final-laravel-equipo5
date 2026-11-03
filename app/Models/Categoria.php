<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    protected $table = 'categorias';

    // [ENCAPSULAMIENTO] Campos permitidos para asignacion masiva.
    protected $fillable = ['nombre', 'descripcion', 'activo'];

    // [ENCAPSULAMIENTO] Conversion de atributos a sus tipos de datos.
    protected $casts = ['activo' => 'boolean'];

    // [RELACION] Asociacion Eloquent con las entidades del restaurante.
    public function platillos(): HasMany
    {
        return $this->hasMany(Platillo::class);
    }
}
