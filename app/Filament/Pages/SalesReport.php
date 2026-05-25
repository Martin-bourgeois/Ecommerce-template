<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Domains\Analytics\Services\AnalyticsService;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Actions;
use Filament\Actions\Action;
use Carbon\Carbon;
use App\Domains\Order\Models\Order;
use League\Csv\Writer;
use SplTempFileObject;

class SalesReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Rapports - Ventes';
    protected static ?string $navigationGroup = 'Rapports';
    protected static string $view = 'filament.pages.sales-report';

    public ?Carbon $fromDate = null;
    public ?Carbon $toDate = null;
    public array $data = [];

    public function mount(): void
    {
        $this->fromDate = now()->startOfMonth();
        $this->toDate = now()->endOfMonth();
        $this->generateReport();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filtres')
                    ->schema([
                        DatePicker::make('fromDate')
                            ->label('Du'),

                        DatePicker::make('toDate')
                            ->label('Au'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function generateReport(): void
    {
        $service = app(AnalyticsService::class);

        $stats = $service->getCustomerStats($this->fromDate ?? now()->startOfMonth(), $this->toDate ?? now());

        $this->data = [
            'total_orders' => $stats['total_orders'],
            'total_revenue' => $stats['total_revenue'],
            'avg_order_value' => $stats['avg_order_value'],
            'new_customers' => $stats['new_customers'],
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('search')
                ->label('Générer le rapport')
                ->action('generateReport'),

            Action::make('export')
                ->label('Exporter en CSV')
                ->color('success')
                ->icon('heroicon-o-arrow-down-tray')
                ->action('exportCsv'),
        ];
    }

    public function exportCsv()
    {
        $csv = Writer::createFromFileObject(new SplTempFileObject());

        $csv->insertOne(['Rapport de ventes', 'Du ' . $this->fromDate->format('d/m/Y') . ' au ' . $this->toDate->format('d/m/Y')]);
        $csv->insertOne([]);

        $orders = Order::whereBetween('created_at', [$this->fromDate, $this->toDate])
            ->where('status', '!=', 'cancelled')
            ->get();

        $csv->insertOne(['N° Commande', 'Client', 'Total', 'Statut', 'Date']);

        foreach ($orders as $order) {
            $csv->insertOne([
                $order->order_number,
                $order->user->name,
                number_format($order->total_amount, 2),
                $order->status,
                $order->created_at->format('d/m/Y H:i'),
            ]);
        }

        $csv->insertOne([]);
        $csv->insertOne(['Total', '', number_format($orders->sum('total_amount'), 2), '', '']);

        $filename = 'ventes_' . $this->fromDate->format('Y-m-d') . '_' . $this->toDate->format('Y-m-d') . '.csv';

        return response()->streamDownload(
            function () use ($csv) {
                echo $csv->getContent();
            },
            $filename,
            ['Content-Type' => 'text/csv']
        );
    }
}
