<?php

use App\Http\Controllers\BulkImportController;
use App\Http\Controllers\ImageUploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/products', [BulkImportController::class, 'index'])->name('products.index');
Route::post('/products/upload-csv', [BulkImportController::class, 'uploadCsv'])->name('products.uploadCsv');
Route::get('/products/csv-progress/{key}', [BulkImportController::class, 'csvProgress']);
Route::get('/products/data', [BulkImportController::class, 'getProductsData'])->name('products.data');

Route::post('/products/images/upload-chunk', [ImageUploadController::class, 'uploadChunk'])->name('products.images.uploadChunk');
Route::post('/products/images/merge-chunks', [ImageUploadController::class, 'mergeChunks'])->name('products.images.mergeChunks');
