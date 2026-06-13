<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CalonPenerimaController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MasterDataController;
use App\Http\Controllers\Api\PenerimaBantuanController;
use App\Http\Controllers\Api\RankingController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    // ===== PUBLIC ROUTES (No authentication required) =====
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login'])->name('auth.login');
    });

    // ===== PROTECTED ROUTES (Require authentication) =====
    Route::middleware('auth:sanctum')->group(function () {
        // ===== Auth Routes =====
        Route::prefix('auth')->group(function () {
            Route::get('me', [AuthController::class, 'me'])->name('auth.me');
            Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
        });

        // ===== User Management =====
        Route::apiResource('users', UserController::class)
            ->names(['index' => 'users.index', 'store' => 'users.store', 'show' => 'users.show', 'update' => 'users.update', 'destroy' => 'users.destroy']);

        // ===== Master Data (Generic CRUD for common entities) =====
        Route::prefix('master-data')->group(function () {
            Route::get('roles', [MasterDataController::class, 'roles'])->name('master-data.roles');

            Route::get('{resource}', [MasterDataController::class, 'index'])->name('master-data.index');
            Route::post('{resource}', [MasterDataController::class, 'store'])->name('master-data.store');
            Route::get('{resource}/{id}', [MasterDataController::class, 'show'])->name('master-data.show');
            Route::put('{resource}/{id}', [MasterDataController::class, 'update'])->name('master-data.update');
            Route::delete('{resource}/{id}', [MasterDataController::class, 'destroy'])->name('master-data.destroy');
        });

        // ===== Calon Penerima (Beneficiary Candidates) =====
        Route::prefix('calon-penerimas')->group(function () {
            Route::get('', [CalonPenerimaController::class, 'index'])->name('calon-penerimas.index');
            Route::post('', [CalonPenerimaController::class, 'store'])->name('calon-penerimas.store');
            Route::post('batch-submit', [CalonPenerimaController::class, 'batchSubmit'])->name('calon-penerimas.batch-submit');
            Route::get('{calonPenerima}', [CalonPenerimaController::class, 'show'])->name('calon-penerimas.show');
            Route::put('{calonPenerima}', [CalonPenerimaController::class, 'update'])->name('calon-penerimas.update');
            Route::delete('{calonPenerima}', [CalonPenerimaController::class, 'destroy'])->name('calon-penerimas.destroy');

            // Workflow Transitions
            Route::post('{calonPenerima}/transition/{action}', [CalonPenerimaController::class, 'transition'])
                ->name('calon-penerimas.transition');
        });

        // ===== SAW Ranking & Decision Making =====
        Route::prefix('ranking')->group(function () {
            Route::post('calculate', [RankingController::class, 'calculate'])->name('ranking.calculate');
            Route::get('results/{periodeBantuan}', [RankingController::class, 'results'])->name('ranking.results');
            Route::post('finalize/{periodeBantuan}', [RankingController::class, 'finalize'])->name('ranking.finalize');
            Route::get('weights/{periodeBantuan}', [RankingController::class, 'getWeights'])->name('ranking.weights');
            Route::put('weights/{periodeBantuan}', [RankingController::class, 'updateWeights'])->name('ranking.weights.update');
        });

        // ===== Reports & Exports =====
        Route::prefix('reports')->group(function () {
            Route::get('candidate-list/{periodeBantuan}', [ReportController::class, 'candidateList'])->name('reports.candidate-list');
            Route::get('ranking-results/{periodeBantuan}', [ReportController::class, 'rankingResults'])->name('reports.ranking-results');
            Route::get('beneficiaries/{periodeBantuan}', [ReportController::class, 'beneficiaryList'])->name('reports.beneficiaries');
            Route::post('surat-permohonan/{periodeBantuan}', [ReportController::class, 'generateSuratPermohonan'])->name('reports.surat-permohonan');
            Route::get('export/{reportType}/{periodeBantuan}', [ReportController::class, 'export'])->name('reports.export');
        });

        // ===== Penerima Bantuan (Final Beneficiaries) =====
        Route::prefix('penerima-bantuans')->group(function () {
            Route::get('', [PenerimaBantuanController::class, 'index'])->name('penerima-bantuans.index');
            Route::get('{penerimaBantuan}', [PenerimaBantuanController::class, 'show'])->name('penerima-bantuans.show');
            Route::put('{penerimaBantuan}', [PenerimaBantuanController::class, 'update'])->name('penerima-bantuans.update');
            Route::post('{penerimaBantuan}/mark-disbursed', [PenerimaBantuanController::class, 'markDisbursed'])->name('penerima-bantuans.mark-disbursed');
        });

        // ===== Dashboard & Analytics =====
        Route::prefix('dashboard')->group(function () {
            Route::get('summary/{periodeBantuan}', [DashboardController::class, 'summary'])->name('dashboard.summary');
            Route::get('statistics/{periodeBantuan}', [DashboardController::class, 'statistics'])->name('dashboard.statistics');
        });
    });
});

// ===== HEALTH CHECK (no auth required) =====
Route::get('/health', function (Request $request) {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});
