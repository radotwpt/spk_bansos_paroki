<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserWebController;
use App\Http\Controllers\KetuaLingkunganStasiController;
use App\Http\Controllers\KetuaLingkunganStasi\CalonPenerimaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Dashboard route (protected)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();
        if ($user && $user->hasRole('ketua_lingkungan_stasi')) {
            return redirect()->route('ketua-lingkungan.dashboard');
        }
        if ($user && $user->hasRole('stasi')) {
            return redirect()->route('stasi.dashboard');
        }
        return view('dashboard');
    })->name('dashboard');

    // Ketua Lingkungan routes
    Route::prefix('ketua-lingkungan')->group(function () {
        Route::get('/dashboard', [KetuaLingkunganStasiController::class, 'dashboard'])->name('ketua-lingkungan.dashboard');

        // Calon Penerima routes for Ketua Lingkungan Stasi
        Route::controller(CalonPenerimaController::class)->prefix('calons')->group(function () {
            Route::get('/', 'index')->name('ketua-lingkungan-stasi.calons.index');
            Route::get('/create', 'create')->name('ketua-lingkungan-stasi.calons.create');
            Route::post('/', 'store')->name('ketua-lingkungan-stasi.calons.store');
            Route::post('/submit-bulk', 'submitBulk')->name('ketua-lingkungan-stasi.calons.submit-bulk');
            Route::get('/{calonPenerima}', 'show')->name('ketua-lingkungan-stasi.calons.show');
            Route::get('/{calonPenerima}/edit', 'edit')->name('ketua-lingkungan-stasi.calons.edit');
            Route::put('/{calonPenerima}', 'update')->name('ketua-lingkungan-stasi.calons.update');
            Route::delete('/{calonPenerima}', 'destroy')->name('ketua-lingkungan-stasi.calons.destroy');
            Route::post('/{calonPenerima}/submit-to-stasi', 'submitToStasi')->name('ketua-lingkungan-stasi.calons.submit-to-stasi');
        });
    });

    // Stasi routes
    Route::prefix('stasi')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\StasiController::class, 'dashboard'])->name('stasi.dashboard');

        // Validasi Calon routes for Stasi
        Route::controller(\App\Http\Controllers\Stasi\ValidasiCalonController::class)->prefix('calons')->group(function () {
            Route::get('/', 'index')->name('stasi.calons.index');
            Route::post('/process-batch', 'processBatch')->name('stasi.calons.process-batch');
            // Route lama cetak surat permohonan dinonaktifkan diganti dengan reports
            // Route::get('/surat-permohonan', 'cetakSuratPermohonan')->name('stasi.calons.surat-permohonan');
            Route::get('/{calonPenerima}', 'show')->name('stasi.calons.show');
        });

        // Manajemen Surat Pengantar routes
        Route::controller(\App\Http\Controllers\Stasi\SuratPermohonanController::class)->prefix('surat-permohonan')->group(function () {
            Route::get('/', 'index')->name('stasi.surat-permohonan.index');
            Route::post('/', 'store')->name('stasi.surat-permohonan.store');
            Route::get('/{suratPermohonan}', 'show')->name('stasi.surat-permohonan.show');
            Route::delete('/{suratPermohonan}', 'destroy')->name('stasi.surat-permohonan.destroy');
            Route::get('/{suratPermohonan}/print', 'print')->name('stasi.surat-permohonan.print');
        });

        // Profil & Pengaturan Stasi routes
        Route::controller(\App\Http\Controllers\Stasi\ProfileController::class)->prefix('profile')->group(function () {
            Route::get('/', 'edit')->name('stasi.profile.edit');
            Route::put('/', 'update')->name('stasi.profile.update');
        });
    });

    // User management routes (for managing users, not candidates)
    Route::get('/users', [UserWebController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserWebController::class, 'create'])->name('users.create');
    Route::post('/users', [UserWebController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserWebController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserWebController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserWebController::class, 'destroy'])->name('users.destroy');
});

