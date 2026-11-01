<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CompanySettingsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'))->name('home');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admins and technicians of the signed-in user's company
    Route::middleware('permission:manage team')->group(function () {
        Route::patch('/team/{member}/toggle', [TeamController::class, 'toggle'])->name('team.toggle');
        Route::resource('team', TeamController::class)->parameters(['team' => 'member'])->except('show');
    });

    Route::middleware('permission:manage company')->group(function () {
        Route::get('/company', [CompanySettingsController::class, 'edit'])->name('company.edit');
        Route::put('/company', [CompanySettingsController::class, 'update'])->name('company.update');
    });

    Route::get('/activity-log', [ActivityLogController::class, 'index'])->middleware('permission:view activity log')->name('activity.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
