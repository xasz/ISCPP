<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Middleware\EnsureNoUsersExists;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\UserManagementController;

Route::middleware('guest')->group(function () {
    

    Volt::route('register', 'auth.register')
        ->name('register')
        ->middleware(EnsureNoUsersExists::class);
    
    Volt::route('login', 'auth.login')
        ->name('login');
    
    Volt::route('forgot-password', 'auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'auth.reset-password')
        ->name('password.reset');

    // Invitation routes
    Route::get('invitation/{token}', [UserManagementController::class, 'showAcceptForm'])
        ->name('invitation.accept');
    
    Route::post('invitation', [UserManagementController::class, 'acceptInvitation'])
        ->name('invitation.process');
});

Route::middleware('auth')->group(function () {
    Volt::route('verify-email', 'auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'auth.confirm-password')
        ->name('password.confirm');
});

Route::post('logout', App\Livewire\Actions\Logout::class)
    ->name('logout');
