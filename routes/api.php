<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\PayController;
use App\Http\Middleware\CheckProjectAccess;
use App\Http\Middleware\CustomCorsMiddleware;
Route::get('/', function () {
    return response()->json([
        'status' => 'success'
    ]);
});


// Route::get('/vendor/auth-url', [VendorController::class, 'authUrl']); // link para o cliente acessar o MP
// Route::get('/vendor/oauth/callback', [VendorController::class, 'oauthCallback']); // callback do MP onde o cliente vai receber o token de acesso

Route::middleware([CustomCorsMiddleware::class,CheckProjectAccess::class])->group(function () {
    Route::get('/vendor', [VendorController::class, 'index']);
    Route::post('/vendor', [VendorController::class, 'store']);
    Route::get('/vendor/{id}', [VendorController::class, 'show']);
    Route::put('/vendor/{id}', [VendorController::class, 'update']);
    Route::delete('/vendor/{id}', [VendorController::class, 'destroy']);

    Route::post('/vendor/newpayment/{id}', [PayController::class, 'store']);
    Route::get('/vendor/payments/{id}', [PayController::class, 'listByVendor']);
    Route::get('/vendor/payment/{id}/{paymentId}', [PayController::class, 'show']);
});
Route::middleware([CustomCorsMiddleware::class])->post('/payment/webhook', [PayController::class, 'notification'])->name('payment.notification');
/**
 * Webhooks e retornos
 */

// Route::middleware(['CustomCorsMiddleware'])->post('/webhook/notification', [PayController::class, 'notification'])->name('client.webhook.notification');
// Route::get('/payment/success/{internalReference}', [PayController::class, 'success'])->name('payment.success');
// Route::get('/payment/failure/{internalReference}', [PayController::class, 'failure'])->name('payment.failure');
// Route::get('/payment/pending/{internalReference}', [PayController::class, 'pending'])->name('payment.pending');