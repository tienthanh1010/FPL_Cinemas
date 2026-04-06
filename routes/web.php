<?php

use App\Http\Controllers\Frontend\BookingController;
use App\Http\Controllers\Frontend\HomeController;
<<<<<<< HEAD
=======
use App\Http\Controllers\Frontend\ReviewController;
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/the-loai/{category}', [HomeController::class, 'category'])->name('category.show');
Route::get('/phim/{movie}/suat-chieu', [HomeController::class, 'showtimes'])->name('movies.showtimes');
<<<<<<< HEAD
=======
Route::post('/phim/{movie}/reviews', [ReviewController::class, 'store'])->name('movies.reviews.store');
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
Route::get('/booking/success/{booking_code}', [BookingController::class, 'success'])->name('booking.success');

require __DIR__ . '/admin.php';
