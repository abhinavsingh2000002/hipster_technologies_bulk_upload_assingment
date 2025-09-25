<?php

use App\Http\Controllers\BulkImportController;
use App\Http\Controllers\ImageUploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view(view: 'product.index');
});

Route::post('/products/upload-csv', [BulkImportController::class, 'uploadCsv'])->name('products.uploadCsv');
Route::get('/products/csv-progress/{key}', [BulkImportController::class, 'csvProgress']);
Route::get('/products/data', [BulkImportController::class, 'getProductsData'])->name('products.data');

Route::post('/images/upload-chunk', [ImageUploadController::class, 'uploadChunk']);
Route::post('/images/finalize', [ImageUploadController::class, 'finalize']);
Route::post('/images/attach', [ImageUploadController::class, 'attach']);
Route::get('/images/process-status/{uploadId}', [ImageUploadController::class, 'processStatus']);
