<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlatilloRequest;
use App\Http\Requests\UpdatePlatilloRequest;
use App\Models\Categoria;
use App\Models\Platillo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PlatilloController extends Controller
{
    /**
     * [CRUD-READ] Listado principal con busqueda, filtros y paginacion.
     * [INYECCION-DEPENDENCIAS] Inyeccion automatica de Request por Laravel.
     */
    public function index(Request $request): View
    {
        $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'categoria_id' => ['nullable', 'integer', 'exists:categorias,id'],
            'disponible' => ['nullable', 'boolean'],
        ], [
            'buscar.string' => 'La búsqueda debe ser texto.',
            'buscar.max' => 'La búsqueda no puede superar los 100 caracteres.',
            'categoria_id.integer' => 'La categoría seleccionada no es válida.',
            'categoria_id.exists' => 'La categoría seleccionada no es válida.',
            'disponible.boolean' => 'La disponibilidad seleccionada no es válida.',
        ]);

        $query = Platillo::with('categoria');

        // Filtro por termino de busqueda (nombre o descripcion)
        if ($request->filled('buscar')) {
            $buscar = trim($request->input('buscar'));
            $query->where(function ($subQuery) use ($buscar) {
                $subQuery->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        }

        // Filtro por categoria del catalogo
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->integer('categoria_id'));
        }

        // Filtro por disponibilidad
        if ($request->filled('disponible') && $request->input('disponible') !== '') {
            $query->where('disponible', $request->boolean('disponible'));
        }

        $platillos = $query->orderBy('id')->paginate(8)->withQueryString();
        $categorias = Categoria::where('activo', true)->orderBy('nombre')->get();

        return view('platillos.index', compact('platillos', 'categorias'));
    }

    /**
     * Muestra el formulario para crear un nuevo platillo.
     */
    public function create(): View
    {
        $categorias = Categoria::where('activo', true)->orderBy('nombre')->get();

        return view('platillos.create', compact('categorias'));
    }

    /**
     * [CRUD-CREATE] Almacena un nuevo platillo en la base de datos.
     * [INYECCION-DEPENDENCIAS] Inyeccion de StorePlatilloRequest validado.
     * [SEGURIDAD] Proteccion masiva con $request->validated().
     */
    public function store(StorePlatilloRequest $request): RedirectResponse
    {
        // [SEGURIDAD] Solo se toman los datos validados por el Form Request
        $datos = $request->validated();

        // Gestion y almacenamiento de fotografia con Storage
        if ($request->hasFile('imagen')) {
            $datos['imagen'] = $request->file('imagen')->store('platillos', 'public');
        }

        $datos['disponible'] = $request->boolean('disponible');

        try {
            $platillo = Platillo::create($datos);
        } catch (\Throwable $exception) {
            if (! empty($datos['imagen'])) {
                Storage::disk('public')->delete($datos['imagen']);
            }
            throw $exception;
        }

        return redirect()->route('platillos.index')
            ->with('exito', 'Platillo "' . $platillo->nombre . '" registrado exitosamente en el menú.');
    }

    /**
     * [CRUD-READ] Muestra la ficha tecnica detallada del platillo.
     * [INYECCION-DEPENDENCIAS] Route Model Binding inyecta la instancia Platillo.
     */
    public function show(Platillo $platillo): View
    {
        // Carga anticipada de la categoria y detalles de pedidos asociados
        $platillo->load(['categoria', 'detallePedidos.pedido']);

        return view('platillos.show', compact('platillo'));
    }

    /**
     * Muestra el formulario de edicion para un platillo existente.
     * [INYECCION-DEPENDENCIAS] Route Model Binding inyecta Platillo.
     */
    public function edit(Platillo $platillo): View
    {
        $categorias = Categoria::orderBy('nombre')->get();

        return view('platillos.edit', compact('platillo', 'categorias'));
    }

    /**
     * [CRUD-UPDATE] Actualiza los datos del platillo en la base de datos.
     * [INYECCION-DEPENDENCIAS] Inyeccion de UpdatePlatilloRequest y Route Model Binding.
     * [SEGURIDAD] Asignacion masiva protegida con validated().
     */
    public function update(UpdatePlatilloRequest $request, Platillo $platillo): RedirectResponse
    {
        $datos = $request->validated();

        $imagenAnterior = $platillo->imagen;
        unset($datos['imagen']);
        // Conservar la foto anterior hasta confirmar la escritura en la base de datos.
        if ($request->hasFile('imagen')) {
            $datos['imagen'] = $request->file('imagen')->store('platillos', 'public');
        }

        $datos['disponible'] = $request->boolean('disponible');

        try {
            $platillo->update($datos);
        } catch (\Throwable $exception) {
            if (! empty($datos['imagen'])) {
                Storage::disk('public')->delete($datos['imagen']);
            }
            throw $exception;
        }

        if ($request->hasFile('imagen') && $imagenAnterior) {
            Storage::disk('public')->delete($imagenAnterior);
        }

        return redirect()->route('platillos.index')
            ->with('exito', 'Platillo "' . $platillo->nombre . '" actualizado correctamente.');
    }

    /**
     * [CRUD-DELETE] Elimina un platillo del menu.
     * [SEGURIDAD] Protegido mediante peticion HTTP DELETE, nunca GET.
     * [REGLA-NEGOCIO] Proteccion de integridad: no borrar si tiene pedidos asociados.
     */
    public function destroy(Platillo $platillo): RedirectResponse
    {
        // Restriccion de negocio e integridad: platillos en pedidos historicos no pueden borrarse
        if ($platillo->detallePedidos()->exists()) {
            return redirect()->route('platillos.index')
                ->with('error', 'No se puede eliminar el platillo "' . $platillo->nombre . '" porque ya está registrado en pedidos del restaurante.');
        }

        $nombre = $platillo->nombre;
        $platillo->delete();

        // Si la BD rechaza el borrado, la fotografia permanece intacta.
        if ($platillo->imagen) {
            Storage::disk('public')->delete($platillo->imagen);
        }

        return redirect()->route('platillos.index')
            ->with('exito', 'Platillo "' . $nombre . '" eliminado del menú satisfactoriamente.');
    }
}
