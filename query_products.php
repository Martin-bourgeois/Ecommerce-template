<?php
require __DIR__ . '/bootstrap/app.php';

$app = new \Illuminate\Foundation\Application(__DIR__);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$products = \App\Domains\Catalog\Models\Product::where('is_active', true)->with('variants')->limit(3)->get();
echo "Products found: " . count($products) . "\n";
foreach ($products as $p) {
    echo "- " . $p->name . " (SKU: " . $p->sku . ", Price: " . ($p->price/100) . ", Variants: " . count($p->variants) . ")\n";
}
