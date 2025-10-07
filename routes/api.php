<?php

use App\Http\Controllers\ZKTekoWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ZKTeko Fingerprint Device Webhook Routes
Route::prefix('webhook/fp')->name('webhook.fp.')->group(function () {
    Route::post('/', [ZKTekoWebhookController::class, 'handle'])->name('handle');
    Route::get('/test', [ZKTekoWebhookController::class, 'test'])->name('test');
});

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'environment' => config('app.env'),
    ]);
});

// Catch-all route for undefined API endpoints
Route::fallback(function () {
    return response()->json([
        'error' => 'Endpoint not found',
        'message' => 'The requested API endpoint does not exist.',
    ], 404);
});
