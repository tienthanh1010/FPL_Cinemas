<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\ContentPost;
use App\Models\ContentRating;
use App\Services\CinemaContextService;
use App\Services\CustomerAccountService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $helpers = app_path('Support/helpers.php');
        if (is_file($helpers)) {
            require_once $helpers;
        }
    }

    public function boot(CinemaContextService $cinemaContextService, CustomerAccountService $customerAccountService): void
    {
        Paginator::useBootstrapFive();

        app()->setLocale(config('app.locale', 'vi'));
        Carbon::setLocale(app()->getLocale());

        View::composer(['frontend.*', 'admin.*'], function ($view) use ($cinemaContextService, $customerAccountService) {
            static $shared = null;

            if ($shared === null) {
                $primaryCinema = Schema::hasTable('cinemas') ? $cinemaContextService->currentCinema() : null;
                $authCustomer = null;

                if (Auth::check() && Schema::hasTable('customers')) {
                    $authCustomer = $customerAccountService->customerForUser(Auth::user());
                }

                $shared = [
                    'appBrand' => $primaryCinema?->name ?: config('app.name', 'FPL Cinema'),
                    'singleCinemaMode' => $cinemaContextService->singleMode(),
                    'primaryCinema' => $primaryCinema,
                    'authCustomer' => $authCustomer,
                ];

                if (Schema::hasTable('content_posts')) {
                    $shared['latestNewsPosts'] = ContentPost::query()
                        ->news()
                        ->visibleOnHome()
                        ->limit(3)
                        ->get();

                    $shared['latestOfferPosts'] = ContentPost::query()
                        ->offers()
                        ->visibleOnHome()
                        ->limit(3)
                        ->get();
                } else {
                    $shared['latestNewsPosts'] = collect();
                    $shared['latestOfferPosts'] = collect();
                }

                if (Schema::hasTable('genres')) {
                    $shared['globalCategories'] = Category::query()
                        ->withCount('movies')
                        ->orderBy('name')
                        ->limit(10)
                        ->get();
                } else {
                    $shared['globalCategories'] = collect();
                }

                if (Schema::hasTable('content_ratings')) {
                    $shared['globalRatings'] = ContentRating::query()
                        ->withCount('movies')
                        ->orderBy('min_age')
                        ->orderBy('name')
                        ->get();
                } else {
                    $shared['globalRatings'] = collect();
                }
            }

            $view->with($shared);
        });
    }
}
