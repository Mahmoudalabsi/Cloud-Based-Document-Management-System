<?php

use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DocumentController::class, 'index'])->name('documents.index');
Route::post('/upload', [DocumentController::class, 'upload'])->name('documents.upload');
Route::get('/search', [DocumentController::class, 'search'])->name('documents.search'); //
Route::get('/sort', [DocumentController::class, 'sort'])->name('documents.sort'); //
Route::get('/stats', [DocumentController::class, 'stats'])->name('documents.stats'); //
Route::post('/classify', [DocumentController::class, 'classify'])->name('documents.classify'); //