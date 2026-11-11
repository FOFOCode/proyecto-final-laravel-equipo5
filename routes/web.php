<?php

use App\Http\Controllers\PlatilloController;
use App\Http\Controllers\PanelController;
use Illuminate\Support\Facades\Route;

// Redireccion inicial al menu principal
Route::redirect('/', '/platillos');

// [SEGURIDAD] CRUD completo de Platillos mediante Resource Controller
Route::resource('platillos', PlatilloController::class);

Route::get('/panel', [PanelController::class, 'index'])->name('panel.index');
