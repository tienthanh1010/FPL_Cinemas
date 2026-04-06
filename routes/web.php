<?php

use App\Http\Controllers\Frontend\BookingController;
use App\Http\Controllers\Frontend\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/the-loai/{category}', [HomeController::class, 'category'])->name('category.show');
Route::get('/phim/{movie}/suat-chieu', [HomeController::class, 'showtimes'])->name('movies.showtimes');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
Route::get('/booking/success/{booking_code}', [BookingController::class, 'success'])->name('booking.success');

require __DIR__ . '/admin.php';
