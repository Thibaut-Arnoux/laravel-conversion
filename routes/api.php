<?php

use App\Http\Controllers\ConversionController;
use App\Http\Controllers\FileController;
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

// oauth
Route::prefix('auth')->as('auth.')->group(function () {
    Route::post('/register', [App\Http\Controllers\AuthController::class, 'register'])->name('register');
    Route::post('/login', [App\Http\Controllers\AuthController::class, 'login'])->name('login');
    Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout')->middleware(['auth:sanctum']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    // files
    Route::apiResource('files', FileController::class)->except(['update']);
    Route::prefix('files')->as('files.')->group(function () {
        Route::get('{file}/convert', [FileController::class, 'convert'])->name('convert');
        Route::get('{file}/download', [FileController::class, 'download'])->name('download');
    });

    // conversions
    Route::apiResource('conversions', ConversionController::class)->only(['index', 'show']);
});
