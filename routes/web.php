<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PostController;

/**
 * -------------------------------------------------------
 * Laravel Authentication Routes (Login, Register, Logout)
 * -------------------------------------------------------
 * Auth::routes() automatically generates:
 * - /login
 * - /register
 * - /logout
 * - password reset routes
 *
 * Required because we are using Laravel UI for authentication.
 */
Auth::routes();

/**
 * -------------------------------------------------------
 * Welcome Route (Home Page)
 * -------------------------------------------------------
 * This route displays the default welcome page.
 * It is publicly accessible.
 */
Route::get('/', function () {
    return view('welcome');
});

/**
 * -------------------------------------------------------
 * Home Route (After Login Redirect)
 * -------------------------------------------------------
 * This route is used by Laravel UI after successful login.
 */
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])
    ->name('home');

/**
 * -------------------------------------------------------
 * Protected Routes (Requires User Login)
 * -------------------------------------------------------
 * The routes inside this group are only accessible when the
 * user is authenticated (logged in).
 *
 * middleware('auth') ensures:
 * - Guests cannot access these routes
 * - auth()->user() is always available inside controllers
 */
Route::middleware('auth')->group(function () {

    Route::get('/posts', [PostController::class, 'index'])
        ->name('posts.index');

    Route::post('/posts', [PostController::class, 'store'])
        ->name('posts.store');

    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::post('/notifications/mark-read/{id}', [App\Http\Controllers\NotificationController::class, 'markAsRead'])
        ->name('notifications.mark-read');

    Route::post('/notifications/mark-unread/{id}', [App\Http\Controllers\NotificationController::class, 'markAsUnread'])
        ->name('notifications.mark-unread');

    Route::get('/notifications/unread-count', [App\Http\Controllers\NotificationController::class, 'unreadCount'])
        ->name('notifications.unread-count');

    Route::get('/send-notification', [App\Http\Controllers\NotificationController::class, 'sendForm'])
        ->name('notifications.send-form');

    Route::post('/send-notification', [App\Http\Controllers\NotificationController::class, 'send'])
        ->name('notifications.send');
});
