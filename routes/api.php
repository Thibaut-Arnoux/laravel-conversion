<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConversionController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ProfileController;
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
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');
    Route::get('/refresh-token', [AuthController::class, 'refreshToken'])->name('token.refresh')
        ->middleware('refresh.token');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::put('/password', [AuthController::class, 'updatePassword'])->name('password.update');
        Route::get('/user', [AuthController::class, 'getAuthenticatedUser'])->name('user');
    });
});

Route::middleware(['auth:sanctum'])->group(function () {
    // profile
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // files
    Route::apiResource('files', FileController::class)->except(['update']);
    Route::prefix('files')->as('files.')->group(function () {
        Route::get('/{file}/convert', [FileController::class, 'convert'])->name('convert');
        Route::get('/{file}/download', [FileController::class, 'download'])->name('download');
    });

    // conversions
    Route::apiResource('conversions', ConversionController::class)->only(['index', 'show']);
});
