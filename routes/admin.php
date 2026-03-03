<?php

use App\Http\Controllers\Admin\AuditoriumController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CinemaChainController;
use App\Http\Controllers\Admin\CinemaController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MovieController;
use App\Http\Controllers\Admin\MovieVersionController;
use App\Http\Controllers\Admin\ShowController;
use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\AdminGuest;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware([AdminGuest::class])->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    });

    Route::post('logout', [AuthController::class, 'logout'])
        ->middleware([AdminAuth::class])
        ->name('logout');

    Route::middleware([AdminAuth::class])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('movies', MovieController::class)->except(['show']);
        Route::resource('movie-versions', MovieVersionController::class)
            ->parameters(['movie-versions' => 'movieVersion'])
            ->names('movie_versions')
            ->except(['show']);
        Route::resource('chains', CinemaChainController::class)->except(['show']);
        Route::resource('cinemas', CinemaController::class)->except(['show']);
        Route::resource('auditoriums', AuditoriumController::class)->except(['show']);
        Route::resource('shows', ShowController::class)->except(['show']);
    });
});
