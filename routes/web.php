<?php

use App\Http\Controllers\PlatilloController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/platillos');
Route::resource('platillos', PlatilloController::class)->only(['index', 'show']);
