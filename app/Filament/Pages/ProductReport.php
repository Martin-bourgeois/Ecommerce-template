<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Domains\Analytics\Services\AnalyticsService;
use App\Domains\Catalog\Models\Product;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\Action;
use Carbon\Carbon;
use League\Csv\Writer;
use SplTempFileObject;

class ProductReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Rapports - Produits';
    protected static ?string $navigationGroup = 'Rapports';
    protected static string $view = 'filament.pages.product-report';

    public ?Carbon $fromDate = null;
    public ?Carbon $toDate = null;
    public array $topProducts = [];
    public array $lowStockProducts = [];

    public function mount(): void
    {
        $this->fromDate = now()->subMonth()->startOfDay();
        $this->toDate = now()->endOfDay();
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
            ]);
    }

    public function generateReport(): void
    {
        $service = app(AnalyticsService::class);

        // Top products
        $this->topProducts = $service->getTopProducts($this->fromDate ?? now()->subMonth(), $this->toDate ?? now(), 10)
            ->map(fn (Product $p) => [
                'name' => $p->name,
                'sku' => $p->sku,
                'quantity' => $p->total_qty ?? 0,
                'price' => $p->price,
                'revenue' => ($p->total_qty ?? 0) * $p->price,
            ])
            ->toArray();

        // Low stock products
        $this->lowStockProducts = Product::selectRaw('products.id, products.name, products.sku, products.price, 
            COALESCE(SUM(product_variants.stock - product_variants.reserved_stock), 0) as total_stock')
            ->leftJoin('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->whereNull('products.deleted_at')
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.price')
            ->havingRaw('COALESCE(SUM(product_variants.stock - product_variants.reserved_stock), 0) <= 5')
            ->orderByRaw('COALESCE(SUM(product_variants.stock - product_variants.reserved_stock), 0) asc')
            ->get()
            ->map(fn (Product $p) => [
                'name' => $p->name,
                'sku' => $p->sku,
                'stock' => $p->total_stock,
                'price' => $p->price,
            ])
            ->toArray();
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

        $csv->insertOne(['Rapport produits', 'Du ' . $this->fromDate->format('d/m/Y') . ' au ' . $this->toDate->format('d/m/Y')]);
        $csv->insertOne([]);

        $csv->insertOne(['PRODUITS LES PLUS VENDUS']);
        $csv->insertOne(['Nom', 'SKU', 'Quantité vendue', 'Prix', 'Revenu']);

        foreach ($this->topProducts as $product) {
            $csv->insertOne([
                $product['name'],
                $product['sku'],
                $product['quantity'],
                number_format($product['price'], 2),
                number_format($product['revenue'], 2),
            ]);
        }

        $csv->insertOne([]);
        $csv->insertOne(['PRODUITS EN STOCK BAS']);
        $csv->insertOne(['Nom', 'SKU', 'Stock', 'Prix']);

        foreach ($this->lowStockProducts as $product) {
            $csv->insertOne([
                $product['name'],
                $product['sku'],
                $product['stock'],
                number_format($product['price'], 2),
            ]);
        }

        $filename = 'produits_' . now()->format('Y-m-d-H-i-s') . '.csv';

        return response()->streamDownload(
            function () use ($csv) {
                echo $csv->getContent();
            },
            $filename,
            ['Content-Type' => 'text/csv']
        );
    }
}
