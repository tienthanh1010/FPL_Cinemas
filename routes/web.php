<?php

use App\Http\Controllers\Frontend\AccountController;
use App\Http\Controllers\Frontend\AuthController as FrontendAuthController;
use App\Http\Controllers\Frontend\BookingController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\ContentController;
use App\Http\Controllers\Frontend\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/the-loai/{category}', [HomeController::class, 'category'])->name('category.show');
Route::get('/phim/{movie}/suat-chieu', [HomeController::class, 'showtimes'])->name('movies.showtimes');
Route::get('/suat-chieu/{show}/dat-ve', [BookingController::class, 'create'])->name('shows.book');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
Route::get('/booking/{booking_code}/payment', [CheckoutController::class, 'show'])->name('booking.payment');
Route::post('/booking/{booking_code}/payment', [CheckoutController::class, 'pay'])->name('booking.payment.pay');
Route::get('/booking/success/{booking_code}', [BookingController::class, 'success'])->name('booking.success');

Route::get('/tin-tuc', [ContentController::class, 'news'])->name('news.index');
Route::get('/tin-tuc/{contentPost:slug}', [ContentController::class, 'showNews'])->name('news.show');
Route::get('/uu-dai', [ContentController::class, 'offers'])->name('offers.index');
Route::get('/uu-dai/{contentPost:slug}', [ContentController::class, 'showOffer'])->name('offers.show');

Route::middleware('guest')->group(function () {
    Route::get('/dang-nhap', [FrontendAuthController::class, 'showLogin'])->name('login');
    Route::post('/dang-nhap', [FrontendAuthController::class, 'login'])->name('member.login.submit');
    Route::get('/dang-ky', [FrontendAuthController::class, 'showRegister'])->name('member.register');
    Route::post('/dang-ky', [FrontendAuthController::class, 'register'])->name('member.register.submit');
});

Route::post('/dang-xuat', [FrontendAuthController::class, 'logout'])->name('member.logout');

Route::middleware('auth')->group(function () {
    Route::get('/tai-khoan', [AccountController::class, 'index'])->name('member.account');
});

require __DIR__ . '/admin.php';
