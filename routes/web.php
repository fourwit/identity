<?php

use Illuminate\Support\Facades\Route;
use Modules\Identity\Http\Controllers\Admin\UserController;
use Modules\Identity\Http\Controllers\Account\ProfileController;
use Modules\Identity\Http\Controllers\Account\PasswordController;
use Modules\Identity\Http\Controllers\Account\VerificationController;
use Modules\Identity\Http\Controllers\Admin\ActivityLogController;

$adminGuard = config('identity.auth.guards.admin', 'web');
$webGuard = config('identity.auth.guards.web', 'web');

Route::middleware(config('identity.routes.middleware.admin', ['web', "auth:{$adminGuard}"]))
    ->prefix(config('identity.routes.admin_prefix', 'admin'))
    ->group(function () {
    Route::resource('users', UserController::class)->names('admin.users');

    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('admin.activity-logs.index');
});



// Self-service account routes
Route::middleware(config('identity.routes.middleware.web', ['web', "auth:{$webGuard}"]))
->prefix('account')
->name('identity.account.')
->group(function () {

    Route::get('/profile', [ProfileController::class, 'show'])
        ->name('profile.show');

    Route::put('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::put('/password', [PasswordController::class, 'update'])
        ->name('password.update');

    Route::delete('/avatar', [ProfileController::class, 'removeAvatar'])
        ->name('avatar.remove');

    Route::get('/verification-status', [VerificationController::class, 'status'])
        ->name('verification.status');
});
