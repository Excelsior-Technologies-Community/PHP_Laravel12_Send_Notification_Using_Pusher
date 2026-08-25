<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\PostController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Auth::routes();

/*
|--------------------------------------------------------------------------
| Welcome
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/

Route::get(
    '/home',
    [App\Http\Controllers\HomeController::class, 'index']
)->name('home');

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Posts
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/posts',
        [PostController::class, 'index']
    )->name('posts.index');

    Route::post(
        '/posts',
        [PostController::class, 'store']
    )->name('posts.store');


    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/notifications',
        [NotificationController::class, 'index']
    )->name('notifications.index');

    Route::get(
        '/notifications/dropdown',
        [NotificationController::class, 'dropdown']
    )->name('notifications.dropdown');


    /*
    |--------------------------------------------------------------------------
    | Mark Read / Unread
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/notifications/mark-read/{id}',
        [NotificationController::class, 'markAsRead']
    )->name('notifications.mark-read');

    Route::post(
        '/notifications/mark-unread/{id}',
        [NotificationController::class, 'markAsUnread']
    )->name('notifications.mark-unread');


    /*
    |--------------------------------------------------------------------------
    | NEW - Mark All As Read
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/notifications/mark-all-read',
        [NotificationController::class, 'markAllAsRead']
    )->name('notifications.mark-all-read');


    /*
    |--------------------------------------------------------------------------
    | NEW - Delete Notification
    |--------------------------------------------------------------------------
    */

    Route::delete(
        '/notifications/{id}',
        [NotificationController::class, 'destroy']
    )->name('notifications.destroy');


    /*
    |--------------------------------------------------------------------------
    | NEW - Delete All Notifications
    |--------------------------------------------------------------------------
    */

    Route::delete(
        '/notifications',
        [NotificationController::class, 'destroyAll']
    )->name('notifications.destroy-all');


    /*
    |--------------------------------------------------------------------------
    | Unread Count
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/notifications/unread-count',
        [NotificationController::class, 'unreadCount']
    )->name('notifications.unread-count');


    /*
    |--------------------------------------------------------------------------
    | Send Notification
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/send-notification',
        [NotificationController::class, 'sendForm']
    )->name('notifications.send-form');

    Route::post(
        '/send-notification',
        [NotificationController::class, 'send']
    )->name('notifications.send');
});
