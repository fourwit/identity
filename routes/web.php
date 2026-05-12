<?php

use Illuminate\Support\Facades\Route;
use Modules\Identity\Http\Controllers\Admin\UserController;
use Modules\Identity\Http\Controllers\Customer\ProfileController;
use Modules\Identity\Http\Controllers\Admin\ActivityLogController;

// ============================================
// TEMPORARY: No auth middleware (for testing)
// We will add proper auth later in Authentication Module
// ===
// ->middleware('auth:admin')

Route::prefix('admin')->middleware('web')->group(function () {
    Route::resource('users', UserController::class)->names('admin.users');

    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('admin.activity-logs.index');
});

// middleware('auth:customer')
Route::group([], function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('customer.profile');
    Route::put('/profile', [ProfileController::class, 'update']);
});



