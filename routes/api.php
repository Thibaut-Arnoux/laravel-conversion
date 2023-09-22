<?php

use App\Http\Controllers\ConversionController;
use App\Http\Controllers\FileController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// files
Route::apiResource('files', FileController::class)->except(['update']);
Route::get('/files/{file}/convert', [FileController::class, 'convert'])->name('files.convert');
Route::get('/files/{file}/download', [FileController::class, 'download'])->name('files.download');

// conversions
Route::apiResource('conversions', ConversionController::class)->only(['index', 'show']);
