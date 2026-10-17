<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BhajanController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\StatsController;
use Illuminate\Support\Facades\Route;

// Public
Route::post('/auth/devotee-login', [AuthController::class, 'devoteeLogin'])
    ->middleware('throttle:10,1');
Route::post('/admin/login', [AdminAuthController::class, 'login'])
    ->middleware('throttle:5,1');
Route::post('/rating', [FeedbackController::class, 'rate'])
    ->middleware('throttle:5,1');
Route::post('/feedback', [FeedbackController::class, 'feedback'])
    ->middleware('throttle:5,1');

Route::get('/debug-db', function () {
    $names = fn ($a) => collect(array_keys((array) $a))
        ->filter(fn ($k) => preg_match('/^(DB_|APP_|ADMIN_|PORT|RAILWAY_)/', $k))
        ->values();

    return [
        'getenv_names'  => $names(getenv()),
        'server_names'  => $names($_SERVER),
        'env_names'     => $names($_ENV),
        'config_cached' => app()->configurationIsCached(),
        'driver'        => \Illuminate\Support\Facades\DB::connection()->getDriverName(),
    ];
});

// Logged in
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/stats', [StatsController::class, 'index']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}/bhajans', [CategoryController::class, 'bhajans']);
    Route::get('/bhajans/{bhajan}', [BhajanController::class, 'show']);

    // Admin only (checked inside each controller)
    Route::post('/admin/announcements', [AnnouncementController::class, 'store']);
    Route::post('/admin/categories', [CategoryController::class, 'store']);
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index']);
    Route::put('/admin/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/admin/categories/{category}', [CategoryController::class, 'destroy']);
    Route::post('/admin/categories/{category}/bhajans', [BhajanController::class, 'store']);
    Route::put('/admin/bhajans/{bhajan}', [BhajanController::class, 'update']);
    Route::delete('/admin/bhajans/{bhajan}', [BhajanController::class, 'destroy']);
    Route::get('/admin/reviews', [AdminDashboardController::class, 'reviews']);
    Route::get('/announcements', [AnnouncementController::class, 'index']);
    Route::get('/announcements/unread-count', [AnnouncementController::class, 'unreadCount']);
    Route::post('/announcements/read', [AnnouncementController::class, 'markRead']);

});