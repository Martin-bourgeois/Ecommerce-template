<?php

declare(strict_types=1);

namespace App\Domains\Returns\Providers;

use App\Domains\Returns\Events\RmaApproved;
use App\Domains\Returns\Events\RmaRefunded;
use App\Domains\Returns\Listeners\SendRmaApprovedNotification;
use App\Domains\Returns\Listeners\SendRmaRefundedNotification;
use App\Domains\Returns\Services\RmaService;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class ReturnServiceProvider extends EventServiceProvider
{
    protected $listen = [
        RmaApproved::class => [
            SendRmaApprovedNotification::class,
        ],
        RmaRefunded::class => [
            SendRmaRefundedNotification::class,
        ],
    ];

    public function register(): void
    {
        $this->app->singleton(RmaService::class, function ($app) {
            return new RmaService();
        });
    }

    public function boot(): void
    {
        //
    }
}
