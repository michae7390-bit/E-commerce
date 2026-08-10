<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;

// Product listing (ProductController is not created by this commit; create or adapt as needed)
Route::get('/', [ProductController::class, 'index'])->name('products.index');

Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('cart.index');
    Route::post('add/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::post('update/{product}', [CartController::class, 'update'])->name('cart.update');
    Route::post('remove/{product}', [CartController::class, 'remove'])->name('cart.remove');
});
