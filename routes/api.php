<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FinancialProfileController;
use App\Http\Controllers\Api\GoalController;
use App\Http\Controllers\Api\GoalContributionController;
use App\Http\Controllers\Api\MonthlyReviewController;
use App\Http\Controllers\Api\SimulatorController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/user', [AuthController::class, 'user'])->name('user');
    
    // Dashboard & Reviews
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/monthly-review', [MonthlyReviewController::class, 'index'])->name('monthly-review');

    // Financial Profile
    Route::prefix('financial-profile')->name('financial-profile.')->group(function () {
        Route::get('/', [FinancialProfileController::class, 'show'])->name('show');
        Route::post('/', [FinancialProfileController::class, 'store'])->name('store');
        Route::put('/', [FinancialProfileController::class, 'update'])->name('update');
    });

    // Goals
    Route::apiResource('goals', GoalController::class);
    
    Route::prefix('goals/{goal}')->name('goals.')->group(function () {
        Route::put('/set-primary', [GoalController::class, 'setPrimary'])->name('set-primary');
        Route::post('/simulate', [SimulatorController::class, 'simulate'])->name('simulate');
        
        // Goal Contributions (Nested)
        Route::prefix('contributions')->name('contributions.')->group(function () {
            Route::get('/', [GoalContributionController::class, 'index'])->name('index');
            Route::post('/', [GoalContributionController::class, 'store'])->name('store');
        });
    });

    // Standalone Contributions
    Route::prefix('contributions')->name('contributions.')->group(function () {
        Route::put('/{contribution}', [GoalContributionController::class, 'update'])->name('update');
        Route::delete('/{contribution}', [GoalContributionController::class, 'destroy'])->name('destroy');
    });
});
