<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/', [ProductController::class, 'index']);
Route::get('/api/products', [ProductController::class, 'fetchRecords']);
Route::post('/api/products', [ProductController::class, 'store']);
Route::put('/api/products/{id}', [ProductController::class, 'update']);
Route::delete('/api/products/{id}', [ProductController::class, 'destroy']);