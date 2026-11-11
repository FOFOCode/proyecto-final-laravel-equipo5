<?php

use App\Http\Controllers\PanelController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PlatilloController;
use Illuminate\Support\Facades\Route;

// Redireccion inicial al menu principal
Route::redirect('/', '/platillos');

// [SEGURIDAD] CRUD completo de Platillos mediante Resource Controller
Route::resource('platillos', PlatilloController::class);

Route::get('/panel', [PanelController::class, 'index'])->name('panel.index');

Route::get('/pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
Route::get('/pedidos/create', [PedidoController::class, 'create'])->name('pedidos.create');
Route::post('/pedidos', [PedidoController::class, 'store'])->name('pedidos.store');
Route::get('/pedidos/{pedido}', [PedidoController::class, 'show'])->name('pedidos.show');
Route::patch('/pedidos/{pedido}/estado', [PedidoController::class, 'updateEstado'])->name('pedidos.updateEstado');
