<?php

use Illuminate\Support\Facades\Route;
use Modules\Identity\Http\Controllers\Api\V1\UserController;
use Modules\Identity\Http\Controllers\Api\V1\ActivityLogController;
use Modules\Identity\Http\Controllers\Customer\ProfileController;
use Modules\Identity\Transformers\UserResource;
use Modules\Identity\Models\User;

$rateLimit = config('identity.api_rate_limit', 60);

// Route::middleware('auth:api')->group(function () {
// ============================================
// API Version 1
// ============================================
Route::prefix('v1')
->middleware(['api', 'throttle:' . $rateLimit . ',1'])
->group(function () {
    Route::apiResource('users', UserController::class);

    Route::get('activity-logs', [ActivityLogController::class, 'index']);
});
