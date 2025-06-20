<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;

Route::get('/', [DocumentController::class, 'index'])->name('documents.index');
Route::get('/stats', [DocumentController::class, 'stats'])->name('documents.stats');
Route::prefix('documents')->group(function () {
    Route::post('/upload', [DocumentController::class, 'upload'])->name('documents.upload');

    Route::get('/search', [DocumentController::class, 'search'])->name('documents.search');

    Route::get('/sort', [DocumentController::class, 'sort'])->name('documents.sort');

    Route::post('/classify', [DocumentController::class, 'classify'])->name('documents.classify');
});
