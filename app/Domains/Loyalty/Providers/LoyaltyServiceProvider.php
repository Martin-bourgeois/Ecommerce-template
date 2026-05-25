<?php

declare(strict_types=1);

namespace App\Domains\Loyalty\Providers;

use App\Domains\Loyalty\Repositories\LoyaltyAccountRepository;
use App\Domains\Loyalty\Repositories\LoyaltyTransactionRepository;
use App\Domains\Loyalty\Services\LoyaltyService;
use Illuminate\Support\ServiceProvider;

class LoyaltyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LoyaltyAccountRepository::class);
        $this->app->singleton(LoyaltyTransactionRepository::class);
        $this->app->singleton(LoyaltyService::class, function ($app) {
            return new LoyaltyService(
                $app->make(LoyaltyAccountRepository::class),
                $app->make(LoyaltyTransactionRepository::class),
            );
        });
    }

    public function boot(): void
    {
        // Register routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/loyalty.php');
    }
}
