<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Expenses
    Route::get('/expenses', [ExpenseController::class, 'index']);
    Route::post('/expenses', [ExpenseController::class, 'store']);
    Route::put('/expenses/{id}', [ExpenseController::class, 'update']);
    Route::delete('/expenses/{id}', [ExpenseController::class, 'destroy']);

    // Reports
    Route::get('/reports/summary', [ReportController::class, 'summary']);
    Route::get('/reports/daily', [ReportController::class, 'daily']);
    Route::get('/reports/category', [ReportController::class, 'byCategory']);
    Route::get('/reports/monthly', [ReportController::class, 'monthly']);

    // Dashboard
    Route::get('/dashboard', [ReportController::class, 'dashboard']);

    // Settings
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile']);
    Route::put('/settings/password', [SettingsController::class, 'updatePassword']);
    Route::put('/settings/notifications', [SettingsController::class, 'updateNotifications']);
    Route::get('/settings/export', [SettingsController::class, 'exportCsv']);
    Route::delete('/settings/account', [SettingsController::class, 'deleteAccount']);
});
