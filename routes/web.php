<?php

use App\Http\Controllers\PlatilloController;
use Illuminate\Support\Facades\Route;

// Redireccion inicial al menu principal
Route::redirect('/', '/platillos');

// [SEGURIDAD] CRUD completo de Platillos mediante Resource Controller
Route::resource('platillos', PlatilloController::class);
