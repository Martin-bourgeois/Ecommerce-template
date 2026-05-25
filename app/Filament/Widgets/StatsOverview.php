<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domains\Analytics\Services\AnalyticsService;
use App\Domains\Support\Models\Ticket;
use App\Domains\Returns\Models\Rma;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $service = app(AnalyticsService::class);
        $today = now()->startOfDay();

        // Revenu d'aujourd'hui
        $revenueToday = $service->getRevenue($today, $today->copy()->endOfDay());

        // Commandes en attente
        $pendingOrders = \App\Domains\Order\Models\Order::where('status', 'pending')->count();

        // Tickets ouverts
        $openTickets = Ticket::where('status', 'open')->count();

        // Retours en cours
        $pendingRmas = Rma::where('status', 'requested')
            ->orWhere('status', 'approved')
            ->orWhere('status', 'received')
            ->count();

        // Nouveaux clients aujourd'hui
        $newCustomersToday = \App\Models\User::whereBetween('created_at', [$today, $today->copy()->endOfDay()])
            ->count();

        // Revenu du mois
        $revenueMonth = $service->getRevenue(now()->startOfMonth(), now()->endOfMonth());

        return [
            Stat::make('CA Aujourd\'hui', '€' . number_format($revenueToday, 2, ',', ' '))
                ->description('Chiffre d\'affaires')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Commandes en attente', $pendingOrders)
                ->description('À traiter')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),

            Stat::make('Tickets ouverts', $openTickets)
                ->description('À assigner')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('warning'),

            Stat::make('Retours en cours', $pendingRmas)
                ->description('À traiter')
                ->descriptionIcon('heroicon-m-arrow-uturn-left')
                ->color('danger'),

            Stat::make('CA Mois', '€' . number_format($revenueMonth, 2, ',', ' '))
                ->description('Chiffre d\'affaires')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),

            Stat::make('Nouveaux clients', $newCustomersToday)
                ->description('Aujourd\'hui')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('success'),
        ];
    }
}
