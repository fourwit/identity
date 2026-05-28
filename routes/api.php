<?php

use Illuminate\Support\Facades\Route;
use Modules\Identity\Http\Controllers\Api\V1\UserController;
use Modules\Identity\Http\Controllers\Api\V1\ActivityLogController;
use Modules\Identity\Http\Controllers\Account\ProfileController;
use Modules\Identity\Http\Controllers\Account\PasswordController;
use Modules\Identity\Http\Controllers\Account\VerificationController;
use Modules\Identity\Transformers\UserResource;

$rateLimit = config('identity.api.rate_limit', 60);

$apiGuard = config('identity.auth.guards.api', 'sanctum');
$apiMiddleware = config('identity.routes.middleware.api', ['api', "auth:{$apiGuard}"]);

Route::middleware(array_merge($apiMiddleware, ['throttle:' . $rateLimit . ',1']))
->group(function () {
    Route::apiResource('users', UserController::class);

    Route::get('activity-logs', [ActivityLogController::class, 'index']);
});

// Self-service account API routes
Route::middleware(array_merge($apiMiddleware, ['throttle:' . $rateLimit . ',1']))
->prefix('account')
->group(function () {
    Route::get('/me', [ProfileController::class, 'me']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/password', [PasswordController::class, 'update']);
    Route::delete('/avatar', [ProfileController::class, 'removeAvatar']);
    Route::get('/verification-status', [VerificationController::class, 'status']);
});
