<?php

use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| These are the routes for the Cloud-Based Document Management System:
| - Upload documents
| - Search documents by keyword
| - Sort documents by title
| - Classify documents based on categories
| - View system statistics
|
*/

// عرض الصفحة الرئيسية مع كل الوثائق
Route::get('/', [DocumentController::class, 'index'])->name('documents.index');

// رفع الملفات (PDF, DOC, DOCX, JPG, PNG) إلى Cloudinary
Route::post('/upload', [DocumentController::class, 'upload'])->name('documents.upload');

// البحث داخل الوثائق حسب الكلمة المفتاحية
Route::get('/search', [DocumentController::class, 'search'])->name('documents.search');

// فرز الوثائق حسب العنوان (title)
Route::get('/sort', [DocumentController::class, 'sort'])->name('documents.sort');

// تصنيف الوثائق بناءً على شجرة التصنيف
Route::post('/classify', [DocumentController::class, 'classify'])->name('documents.classify');

// عرض إحصائيات النظام (عدد الملفات، الحجم الكلي، زمن العمليات)
Route::get('/stats', [DocumentController::class, 'stats'])->name('documents.stats');