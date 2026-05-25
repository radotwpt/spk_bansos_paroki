<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentTemplateController;
use App\Http\Controllers\Api\GeneratedLetterController;
use App\Http\Controllers\Api\KetuaLingkunganParokiController;
use App\Http\Controllers\Api\KetuaLingkunganStasiController;
use App\Http\Controllers\Api\OfflineSyncController;
use App\Http\Controllers\Api\ParokiController;
use App\Http\Controllers\Api\StasiController;
use App\Http\Controllers\Api\Master\StasiController as MasterStasiController;
use App\Http\Controllers\Api\Master\LingkunganStasiController as MasterLingkunganStasiController;
use App\Http\Controllers\Api\Master\LingkunganParokiController as MasterLingkunganParokiController;
use App\Http\Controllers\Api\Master\BansosPeriodController as MasterBansosPeriodController;
use App\Http\Controllers\Api\Master\UserController as MasterUserController;
use Illuminate\Support\Facades\Route;

Route::post('/v1/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/v1/auth/me', [AuthController::class, 'me']);
    Route::post('/v1/auth/logout', [AuthController::class, 'logout']);

    // Ketua Lingkungan Stasi
    Route::middleware(['role:ketua_lingkungan_stasi,super_admin'])->prefix('v1/lingkungan-stasi')->group(function () {
        Route::get('/calon-penerima', [KetuaLingkunganStasiController::class, 'index']);
        Route::post('/calon-penerima', [KetuaLingkunganStasiController::class, 'store']);
        Route::put('/calon-penerima/{id}', [KetuaLingkunganStasiController::class, 'update']);
        Route::delete('/calon-penerima/{id}', [KetuaLingkunganStasiController::class, 'destroy']);
        Route::post('/calon-penerima/{id}/ajukan', [KetuaLingkunganStasiController::class, 'submitToStasi']);
    });

    // Stasi
    Route::middleware(['role:stasi,super_admin'])->prefix('v1/stasi')->group(function () {
        Route::get('/calon-penerima-rekap', [StasiController::class, 'indexCalonPenerima']);
        Route::post('/calon-penerima/{id}/approve', [StasiController::class, 'approveByStasi']);
        Route::post('/surat-permohonan/generate', [StasiController::class, 'generateSuratPermohonan']);
        Route::put('/template-surat', [StasiController::class, 'updateTemplateSurat']);
    });

    // Ketua Lingkungan Paroki
    Route::middleware(['role:ketua_lingkungan_paroki,super_admin'])->prefix('v1/lingkungan-paroki')->group(function () {
        Route::post('/proses-saw/{periodId}', [KetuaLingkunganParokiController::class, 'executeSawRanking']);
        Route::post('/kirim-ke-paroki/{periodId}', [KetuaLingkunganParokiController::class, 'sendRankingToParoki']);
    });

    // Paroki
    Route::middleware(['role:paroki,super_admin'])->prefix('v1/paroki')->group(function () {
        Route::get('/ranking-data/{periodId}', [ParokiController::class, 'viewRankedData']);
        Route::post('/penerima/{id}/keputusan', [ParokiController::class, 'finalizeDecision']);
        Route::post('/surat-edaran/generate', [ParokiController::class, 'generateSuratEdaran']);
        Route::put('/template-edaran', [ParokiController::class, 'updateTemplateEdaran']);

        // Document templates & generated letters
        Route::apiResource('/templates', DocumentTemplateController::class);
        Route::post('/templates/{id}/render/{calonId?}', [DocumentTemplateController::class, 'render']);
        Route::post('/surat/generate', [GeneratedLetterController::class, 'generateFromTemplate']);
        Route::get('/surat', [GeneratedLetterController::class, 'index']);
    });

    // Master data (super_admin)
    Route::middleware(['role:super_admin'])->prefix('v1/master')->group(function () {
        Route::apiResource('/stasis', MasterStasiController::class);
        Route::apiResource('/lingkungan-stasis', MasterLingkunganStasiController::class);
        Route::apiResource('/lingkungan-parokis', MasterLingkunganParokiController::class);
        Route::apiResource('/bansos-periods', MasterBansosPeriodController::class);
        Route::apiResource('/users', MasterUserController::class);
    });

    // Offline sync endpoint (used by PWA IndexedDB drain)
    Route::middleware(['role:ketua_lingkungan_stasi,super_admin'])->post('/v1/offline/sync', [OfflineSyncController::class, 'sync']);
});
