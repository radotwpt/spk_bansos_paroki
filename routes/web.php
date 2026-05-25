<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
});

use App\Http\Controllers\PwaController;
Route::get('/pwa', [PwaController::class, 'index']);
use App\Http\Controllers\FrontendController;
Route::get('/app', [FrontendController::class, 'app']);
