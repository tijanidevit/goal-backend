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
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/user', [AuthController::class, 'user']);
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/monthly-review', [MonthlyReviewController::class, 'index'])->name('monthly-review');

    Route::get('/financial-profile', [FinancialProfileController::class, 'show'])->name('financial-profile.show');
    Route::post('/financial-profile', [FinancialProfileController::class, 'store'])->name('financial-profile.store');
    Route::put('/financial-profile', [FinancialProfileController::class, 'update'])->name('financial-profile.update');

    Route::apiResource('goals', GoalController::class);
    Route::put('/goals/{goal}/set-primary', [GoalController::class, 'setPrimary'])->name('goals.set-primary');
    Route::post('/goals/{goal}/simulate', [SimulatorController::class, 'simulate'])->name('goals.simulate');
    
    Route::get('/goals/{goal}/contributions', [GoalContributionController::class, 'index'])->name('goals.contributions.index');
    Route::post('/goals/{goal}/contributions', [GoalContributionController::class, 'store'])->name('goals.contributions.store');
    Route::put('/contributions/{contribution}', [GoalContributionController::class, 'update'])->name('contributions.update');
    Route::delete('/contributions/{contribution}', [GoalContributionController::class, 'destroy'])->name('contributions.destroy');
});
