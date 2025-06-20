<?php

use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;

// المسار الرئيسي لعرض الوثائق (يتوقع view)
Route::get('/', [DocumentController::class, 'index'])->name('documents.index');
// مسار رفع الملف (نموذج ويب)
Route::post('/upload', [DocumentController::class, 'upload'])->name('documents.upload');
// مسارات البحث والترتيب والإحصائيات إذا كنت تريد أن تظل صفحات ويب تعرض بيانات منظمة
Route::get('/search', [DocumentController::class, 'search'])->name('documents.search'); //
Route::get('/sort', [DocumentController::class, 'sort'])->name('documents.sort'); //
Route::get('/stats', [DocumentController::class, 'stats'])->name('documents.stats'); //
// مسار التصنيف (إذا كان يتم إرساله من نموذج ويب)
Route::post('/classify', [DocumentController::class, 'classify'])->name('documents.classify'); //