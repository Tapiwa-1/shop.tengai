<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/product-images/{path}', [ProductController::class, 'image'])
    ->where('path', '.*')
    ->name('products.image');
