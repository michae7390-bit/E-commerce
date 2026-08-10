<?php

use Illuminate\Support\Facades\Route;

// Include ecommerce routes
require __DIR__ . '/ecommerce.php';

// Checkout & Orders
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StripeWebhookController;

Route::get('checkout', [OrderController::class, 'checkout'])->name('checkout.index');
Route::post('checkout', [OrderController::class, 'store'])->name('checkout.store');

// Stripe webhook endpoint (no CSRF)
Route::post('webhook/stripe', [StripeWebhookController::class, 'handle'])->name('webhook.stripe')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
