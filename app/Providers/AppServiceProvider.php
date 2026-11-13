<?php

namespace App\Providers;

use App\Models\Product;
use App\Observers\ProductObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Services\Storefront\PaymentGateway::class, \App\Services\Storefront\RazorpayTestGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\RateLimiter::for('customer-auth', fn ($request) => [
            \Illuminate\Cache\RateLimiting\Limit::perMinute(10)->by(hash('sha256', $request->ip().'|'.json_encode($request->input('mobile') ?? $request->input('phone')))),
            \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($request->ip()),
        ]);
        \Illuminate\Support\Facades\RateLimiter::for('customer-api', fn ($request) => \Illuminate\Cache\RateLimiting\Limit::perMinute(300)->by($request->user()?->customer_id ?? $request->ip()));
        \Illuminate\Support\Facades\RateLimiter::for('customer-checkout', fn ($request) => \Illuminate\Cache\RateLimiting\Limit::perMinute(30)->by($request->user()?->customer_id ?? $request->ip()));
        Schema::defaultStringLength(200);
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        Paginator::useBootstrapFive();
        Product::observe(ProductObserver::class);
    }
}
