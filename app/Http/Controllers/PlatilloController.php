<?php

namespace App\Http\Controllers;

use App\Models\Platillo;
use Illuminate\View\View;

class PlatilloController extends Controller
{
    // [CRUD-READ] Listado paginado con carga anticipada de categorias.
    public function index(): View
    {
        $platillos = Platillo::with('categoria')->orderBy('id')->paginate(8);

        return view('platillos.index', compact('platillos'));
    }

    // [CRUD-READ] Ficha de detalle mediante Route Model Binding.
    public function show(Platillo $platillo): View
    {
        $platillo->load('categoria');

        return view('platillos.show', compact('platillo'));
    }
}
