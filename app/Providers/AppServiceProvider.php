<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->register(\App\Domains\Cart\Providers\CartServiceProvider::class);
        $this->app->register(\App\Domains\Loyalty\Providers\LoyaltyServiceProvider::class);
        $this->app->register(\App\Domains\Returns\Providers\ReturnServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register view composers
        \Illuminate\Support\Facades\View::composer(
            'layouts.app',
            \App\View\Composers\CategoryComposer::class
        );
    }
}
