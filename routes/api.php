<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BhajanController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\StatsController;
use Illuminate\Support\Facades\Route;

// ---- Public: devotees don't log in ----
Route::post('/devices', [DeviceController::class, 'register'])->middleware('throttle:30,1');

Route::get('/stats', [StatsController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}/bhajans', [CategoryController::class, 'bhajans']);
Route::get('/bhajans/{bhajan}', [BhajanController::class, 'show']);
Route::get('/announcements', [AnnouncementController::class, 'index']);
Route::get('/announcements/unread-count', [AnnouncementController::class, 'unreadCount']);

Route::post('/rating', [FeedbackController::class, 'rate'])->middleware('throttle:5,1');
Route::post('/feedback', [FeedbackController::class, 'feedback'])->middleware('throttle:5,1');

// ---- Admin login ----
Route::post('/admin/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1');

// ---- Admin only ----
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index']);
    Route::get('/admin/reviews', [AdminDashboardController::class, 'reviews']);

    Route::post('/admin/announcements', [AnnouncementController::class, 'store']);

    Route::post('/admin/categories', [CategoryController::class, 'store']);
    Route::put('/admin/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/admin/categories/{category}', [CategoryController::class, 'destroy']);

    Route::post('/admin/categories/{category}/bhajans', [BhajanController::class, 'store']);
    Route::put('/admin/bhajans/{bhajan}', [BhajanController::class, 'update']);
    Route::delete('/admin/bhajans/{bhajan}', [BhajanController::class, 'destroy']);
});