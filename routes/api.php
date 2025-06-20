<?php

use App\Http\Controllers\DocumentController;
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

// مسار افتراضي للمستخدم المصادق عليه (مثال)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) { //
    return $request->user(); //
});

// مسارات API لمشروع المستندات
// لاحظ استخدام prefix 'documents' لتنظيم أفضل
Route::prefix('documents')->group(function () {
    Route::get('/', [DocumentController::class, 'index'])->name('api.documents.index');
    Route::post('/upload', [DocumentController::class, 'upload'])->name('api.documents.upload');
    Route::get('/search', [DocumentController::class, 'search'])->name('api.documents.search');
    Route::get('/sort', [DocumentController::class, 'sort'])->name('api.documents.sort');
    Route::post('/classify', [DocumentController::class, 'classify'])->name('api.documents.classify');
    Route::get('/stats', [DocumentController::class, 'stats'])->name('api.documents.stats');
});
