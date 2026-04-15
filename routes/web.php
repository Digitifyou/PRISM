<?php

use App\Http\Controllers\ContentPlanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InsightController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientPillarController;

// Dashboard
Route::get('/', fn () => redirect()->route('dashboard'));
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Clients (Level 0)
Route::post('/clients/discover',    [ClientController::class, 'discover'])->name('clients.discover');
Route::resource('clients', ClientController::class)->only(['index', 'store', 'update', 'destroy']);
Route::post('/pillars/generate',   [ClientPillarController::class, 'generate'])->name('pillars.generate');
Route::post('/pillars/bulk-store', [ClientPillarController::class, 'bulkStore'])->name('pillars.bulk-store');
Route::resource('pillars', ClientPillarController::class)->only(['index', 'store']);

// Content Plans
Route::get('/plans',           [ContentPlanController::class, 'index'])->name('plans.index');
Route::post('/plans',          [ContentPlanController::class, 'store'])->name('plans.store');
Route::delete('/plans/{plan}', [ContentPlanController::class, 'destroy'])->name('plans.destroy');

// Posts
Route::get('/calendar',                           [PostController::class, 'calendar'])->name('calendar');
Route::get('/posts',                              [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{post}',                       [PostController::class, 'show'])->name('posts.show');
Route::patch('/posts/{post}',                     [PostController::class, 'update'])->name('posts.update');
Route::patch('/posts/{post}/approve',             [PostController::class, 'approve'])->name('posts.approve');
Route::patch('/posts/{post}/reject',              [PostController::class, 'reject'])->name('posts.reject');
Route::post('/posts/bulk-approve',                [PostController::class, 'bulkApprove'])->name('posts.bulk-approve');
Route::post('/posts/{post}/regenerate-image',     [PostController::class, 'regenerateImage'])->name('posts.regenerate-image');
Route::post('/posts/{post}/generate-poster-copy', [PostController::class, 'generatePosterCopy'])->name('posts.generate-poster-copy');
Route::delete('/posts/{post}',                    [PostController::class, 'destroy'])->name('posts.destroy');

// Insights
Route::get('/insights', [InsightController::class, 'index'])->name('insights.index');

// Settings
Route::get('/settings',  [SettingsController::class, 'index'])->name('settings.index');
Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
