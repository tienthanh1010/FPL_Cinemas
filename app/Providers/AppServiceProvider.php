<?php

namespace App\Providers;

<<<<<<< HEAD
use App\Models\Category;
use App\Models\ContentPost;
use App\Services\CinemaContextService;
use App\Services\CustomerAccountService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
=======
use Illuminate\Pagination\Paginator;
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
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
<<<<<<< HEAD

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
                    'appBrand' => config('app.name', 'FPL Cinemas'),
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

                if (Schema::hasTable('categories')) {
                    $shared['globalCategories'] = Category::query()
                        ->withCount('movies')
                        ->orderBy('name')
                        ->limit(10)
                        ->get();
                } else {
                    $shared['globalCategories'] = collect();
                }
            }

            $view->with($shared);
        });
=======
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
    }
}
