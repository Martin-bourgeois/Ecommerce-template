<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domains\Analytics\Services\AnalyticsService;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Chiffre d\'affaires';
    protected static ?int $sort = 1;

    public ?string $filter = '7days';

    protected function getFilters(): ?array
    {
        return [
            '7days' => '7 derniers jours',
            '30days' => '30 derniers jours',
            'year' => 'Cette année',
        ];
    }

    protected function getData(): array
    {
        $service = app(AnalyticsService::class);

        $data = match ($this->filter) {
            '30days' => $this->getLast30DaysData($service),
            'year' => $this->getYearData($service),
            default => $this->getLast7DaysData($service),
        };

        return [
            'datasets' => [
                [
                    'label' => 'Revenu (€)',
                    'data' => array_values($data),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
            ],
            'labels' => array_keys($data),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function getLast7DaysData(AnalyticsService $service): array
    {
        return $service->getRevenueByDay(7);
    }

    private function getLast30DaysData(AnalyticsService $service): array
    {
        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $key = $date->format('Y-m-d');
            $cacheKey = "analytics:revenue_day:{$key}";

            $data[$date->format('d/m')] = \Illuminate\Support\Facades\Cache::remember(
                $cacheKey,
                5 * 60,
                function () use ($date) {
                    return (float) \App\Domains\Order\Models\Order::whereBetween(
                        'created_at',
                        [$date->startOfDay(), $date->endOfDay()]
                    )
                        ->where('status', '!=', 'cancelled')
                        ->sum('total_amount');
                }
            );
        }

        return $data;
    }

    private function getYearData(AnalyticsService $service): array
    {
        $data = [];
        for ($m = 1; $m <= 12; $m++) {
            $from = Carbon::create(now()->year, $m, 1)->startOfMonth();
            $to = $from->copy()->endOfMonth();
            $label = $from->format('M');

            $data[$label] = $service->getRevenue($from, $to);
        }

        return $data;
    }
}
