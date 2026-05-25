<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Models\Attribute;
use App\Domains\Catalog\Models\AttributeOption;
use App\Domains\Catalog\Models\ProductAttributeValue;
use App\Domains\Catalog\Enums\ProductType;
use App\Domains\Catalog\Enums\ProductStatus;

class CatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create root categories
        $categories = [
            ['name' => 'Vêtements', 'slug' => 'vetements'],
            ['name' => 'Électronique', 'slug' => 'electronique'],
            ['name' => 'Maison', 'slug' => 'maison'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['slug' => $cat['slug']],
                ['name' => $cat['name'], 'is_active' => true]
            );
        }

        // Create attributes
        $sizeAttr = Attribute::firstOrCreate(
            ['slug' => 'taille'],
            ['name' => 'Taille', 'type' => 'select', 'is_filterable' => true]
        );

        $colorAttr = Attribute::firstOrCreate(
            ['slug' => 'couleur'],
            ['name' => 'Couleur', 'type' => 'select', 'is_filterable' => true]
        );

        $materialAttr = Attribute::firstOrCreate(
            ['slug' => 'matiere'],
            ['name' => 'Matière', 'type' => 'select', 'is_filterable' => true]
        );

        // Create attribute options
        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
        foreach ($sizes as $index => $size) {
            AttributeOption::firstOrCreate(
                ['attribute_id' => $sizeAttr->id, 'value' => strtolower($size)],
                ['label' => $size, 'sort_order' => $index]
            );
        }

        $colors = [
            ['label' => 'Rouge', 'value' => 'rouge', 'color' => '#FF0000'],
            ['label' => 'Bleu', 'value' => 'bleu', 'color' => '#0000FF'],
            ['label' => 'Noir', 'value' => 'noir', 'color' => '#000000'],
            ['label' => 'Blanc', 'value' => 'blanc', 'color' => '#FFFFFF'],
            ['label' => 'Vert', 'value' => 'vert', 'color' => '#00FF00'],
            ['label' => 'Jaune', 'value' => 'jaune', 'color' => '#FFFF00'],
        ];
        foreach ($colors as $index => $color) {
            AttributeOption::firstOrCreate(
                ['attribute_id' => $colorAttr->id, 'value' => $color['value']],
                ['label' => $color['label'], 'color' => $color['color'], 'sort_order' => $index]
            );
        }

        $materials = [
            ['label' => 'Coton 100%', 'value' => 'coton_100'],
            ['label' => 'Coton/Polyester', 'value' => 'coton_poly'],
            ['label' => 'Soie', 'value' => 'soie'],
            ['label' => 'Laine', 'value' => 'laine'],
        ];
        foreach ($materials as $index => $material) {
            AttributeOption::firstOrCreate(
                ['attribute_id' => $materialAttr->id, 'value' => $material['value']],
                ['label' => $material['label'], 'sort_order' => $index]
            );
        }

        // Create demo products (50 total: 30 simple + 20 configurable)
        $clothingCategory = Category::where('slug', 'vetements')->first();
        $electronicsCategory = Category::where('slug', 'electronique')->first();
        $homeCategory = Category::where('slug', 'maison')->first();

        // Simple products
        $this->createSimpleProducts($clothingCategory, 15);
        $this->createSimpleProducts($electronicsCategory, 10);
        $this->createSimpleProducts($homeCategory, 5);

        // Configurable products (T-shirt variants)
        $this->createConfigurableTShirts($clothingCategory, 10, $sizeAttr, $colorAttr);
        $this->createConfigurableJackets($clothingCategory, 10, $sizeAttr, $colorAttr, $materialAttr);
    }

    private function createSimpleProducts(Category $category, int $count): void
    {
        $simpleProductsData = [
            'Vêtements' => [
                ['name' => 'T-shirt Classique Coton', 'price' => 2999, 'description' => 'T-shirt confortable en coton 100%'],
                ['name' => 'T-shirt Premium Coton Bio', 'price' => 3999, 'description' => 'T-shirt écologique en coton biologique'],
                ['name' => 'Polo Manches Courtes', 'price' => 4999, 'description' => 'Polo élégant pour toutes les occasions'],
                ['name' => 'Sweatshirt Coton', 'price' => 5999, 'description' => 'Sweatshirt chaud et confortable'],
                ['name' => 'Pantalon Denim Bleu', 'price' => 7999, 'description' => 'Jean classique bleu indigo'],
                ['name' => 'Pantalon Chino Gris', 'price' => 6999, 'description' => 'Pantalon chino gris élégant'],
                ['name' => 'Chemise Oxford Blanc', 'price' => 8999, 'description' => 'Chemise oxford blanc intemporelle'],
                ['name' => 'Chemise Carreaux Rouge', 'price' => 7999, 'description' => 'Chemise carreaux rouge et blanc'],
                ['name' => 'Short Été Coton', 'price' => 3999, 'description' => 'Short léger pour l\'été'],
                ['name' => 'Gilet Cachemire', 'price' => 12999, 'description' => 'Gilet luxe en cachemire pur'],
                ['name' => 'Écharpe Laine', 'price' => 4999, 'description' => 'Écharpe chaude en laine mérinos'],
                ['name' => 'Bonnet Tricoté', 'price' => 2999, 'description' => 'Bonnet tricoté pour l\'hiver'],
                ['name' => 'Gants Cuir', 'price' => 5999, 'description' => 'Gants en cuir véritable'],
                ['name' => 'Ceinture Cuir Marron', 'price' => 4999, 'description' => 'Ceinture cuir marron classe'],
                ['name' => 'Chaussettes Coton', 'price' => 1299, 'description' => 'Lot de 3 paires de chaussettes'],
            ],
            'Électronique' => [
                ['name' => 'Casque Bluetooth Noise-Cancelling', 'price' => 19999, 'description' => 'Casque haut de gamme avec réduction bruit'],
                ['name' => 'Écouteurs Bluetooth Sans Fil', 'price' => 12999, 'description' => 'Écouteurs compacts et confortables'],
                ['name' => 'Batterie Externe 20000mAh', 'price' => 4999, 'description' => 'Batterie externe haute capacité'],
                ['name' => 'Chargeur USB-C 65W', 'price' => 3999, 'description' => 'Chargeur rapide USB-C'],
                ['name' => 'Câble USB-C 2m', 'price' => 1999, 'description' => 'Câble USB-C haute qualité'],
                ['name' => 'Souris Sans Fil', 'price' => 2999, 'description' => 'Souris ergonomique sans fil'],
                ['name' => 'Clavier Mécanique RGB', 'price' => 14999, 'description' => 'Clavier mécanique rétroéclairé RGB'],
                ['name' => 'Webcam 1080p USB', 'price' => 7999, 'description' => 'Webcam Full HD pour vidéoconférence'],
                ['name' => 'Microphone Condensateur USB', 'price' => 8999, 'description' => 'Microphone professionnel USB'],
                ['name' => 'Support Téléphone Voiture', 'price' => 2499, 'description' => 'Support magnétique universel'],
            ],
            'Maison' => [
                ['name' => 'Coussin Décoratif Gris', 'price' => 2999, 'description' => 'Coussin confortable pour canapé'],
                ['name' => 'Plaid Polaire Beige', 'price' => 4999, 'description' => 'Plaid chaud et douillet'],
                ['name' => 'Lampe de Bureau LED', 'price' => 5999, 'description' => 'Lampe LED ajustable pour le bureau'],
                ['name' => 'Tapis Géométrique', 'price' => 8999, 'description' => 'Tapis moderne géométrique'],
                ['name' => 'Cadre Photo Blanc', 'price' => 1999, 'description' => 'Cadre photo élégant blanc'],
            ],
        ];

        $categoryName = $category->name;
        $products = $simpleProductsData[$categoryName] ?? [];

        foreach (array_slice($products, 0, $count) as $productData) {
            $product = Product::create([
                'category_id' => $category->id,
                'name' => $productData['name'],
                'slug' => \Illuminate\Support\Str::slug($productData['name']) . '-' . uniqid(),
                'description' => $productData['description'],
                'type' => ProductType::SIMPLE,
                'status' => ProductStatus::ACTIVE,
                'sku' => strtoupper(\Illuminate\Support\Str::slug($productData['name'])),
                'price' => $productData['price'],
                'cost' => (int) ($productData['price'] * 0.4),
                'is_featured' => rand(0, 1) === 1,
            ]);

            // Create variant for simple product
            ProductVariant::create([
                'product_id' => $product->id,
                'name' => $productData['name'],
                'sku' => $this->generateSku($category, $product->id, 1),
                'price' => $productData['price'],
                'cost' => (int) ($productData['price'] * 0.4),
                'stock' => rand(10, 100),
                'is_active' => true,
            ]);
        }
    }

    private function createConfigurableTShirts(
        Category $category,
        int $count,
        Attribute $sizeAttr,
        Attribute $colorAttr
    ): void {
        $tshirtNames = [
            'T-Shirt Fitted Noir',
            'T-Shirt Oversize Blanc',
            'T-Shirt Casual Gris',
            'T-Shirt Premium Bleu',
            'T-Shirt Vintage Rouge',
            'T-Shirt Sport Vert',
            'T-Shirt Urban Jaune',
            'T-Shirt Classic Beige',
            'T-Shirt Modern Bleu Marine',
            'T-Shirt Bold Orange',
        ];

        $sizeOptions = $sizeAttr->options->slice(1, 4); // S, M, L, XL
        $colorOptions = $colorAttr->options->slice(0, 3); // Red, Blue, Black

        foreach (array_slice($tshirtNames, 0, $count) as $index => $name) {
            $product = Product::create([
                'category_id' => $category->id,
                'name' => $name,
                'slug' => \Illuminate\Support\Str::slug($name) . '-' . uniqid(),
                'description' => 'T-shirt configurable avec options de taille et couleur',
                'type' => ProductType::CONFIGURABLE,
                'status' => ProductStatus::ACTIVE,
                'sku' => 'TSHIRT-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'price' => 3499,
                'cost' => 1400,
                'is_featured' => rand(0, 1) === 1,
            ]);

            $variantIndex = 1;
            foreach ($sizeOptions as $sizeOption) {
                foreach ($colorOptions as $colorOption) {
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'name' => "{$sizeOption->label} - {$colorOption->label}",
                        'sku' => $this->generateSku($category, $product->id, $variantIndex),
                        'price' => 3499,
                        'cost' => 1400,
                        'stock' => rand(20, 150),
                        'is_active' => true,
                        'sort_order' => $variantIndex - 1,
                    ]);

                    // Create attribute values
                    ProductAttributeValue::create([
                        'product_variant_id' => $variant->id,
                        'attribute_id' => $sizeAttr->id,
                        'attribute_option_id' => $sizeOption->id,
                    ]);

                    ProductAttributeValue::create([
                        'product_variant_id' => $variant->id,
                        'attribute_id' => $colorAttr->id,
                        'attribute_option_id' => $colorOption->id,
                    ]);

                    $variantIndex++;
                }
            }
        }
    }

    private function createConfigurableJackets(
        Category $category,
        int $count,
        Attribute $sizeAttr,
        Attribute $colorAttr,
        Attribute $materialAttr
    ): void {
        $jacketNames = [
            'Veste Bomber Noir',
            'Veste Denim Classic',
            'Veste Blazer Blanc',
            'Veste Parka Gris',
            'Veste Cuir Marron',
            'Veste Jean Bleu',
            'Veste Sport Bleu Marine',
            'Veste Casual Kaki',
            'Veste Élégante Noir',
            'Veste Urban Gris Foncé',
        ];

        $sizeOptions = $sizeAttr->options->slice(0, 4); // XS, S, M, L
        $colorOptions = $colorAttr->options->slice(0, 2); // Red, Blue
        $materialOptions = $materialAttr->options->slice(0, 2); // Cotton 100%, Cotton/Poly

        foreach (array_slice($jacketNames, 0, $count) as $index => $name) {
            $product = Product::create([
                'category_id' => $category->id,
                'name' => $name,
                'slug' => \Illuminate\Support\Str::slug($name) . '-' . uniqid(),
                'description' => 'Veste configurable avec options de taille, couleur et matière',
                'type' => ProductType::CONFIGURABLE,
                'status' => ProductStatus::ACTIVE,
                'sku' => 'JACKET-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'price' => 14999,
                'cost' => 5999,
                'is_featured' => rand(0, 1) === 1,
            ]);

            $variantIndex = 1;
            foreach ($sizeOptions as $sizeOption) {
                foreach ($colorOptions as $colorOption) {
                    foreach ($materialOptions as $materialOption) {
                        $variant = ProductVariant::create([
                            'product_id' => $product->id,
                            'name' => "{$sizeOption->label} - {$colorOption->label} - {$materialOption->label}",
                            'sku' => $this->generateSku($category, $product->id, $variantIndex),
                            'price' => 14999,
                            'cost' => 5999,
                            'stock' => rand(15, 100),
                            'is_active' => true,
                            'sort_order' => $variantIndex - 1,
                        ]);

                        // Create attribute values
                        ProductAttributeValue::create([
                            'product_variant_id' => $variant->id,
                            'attribute_id' => $sizeAttr->id,
                            'attribute_option_id' => $sizeOption->id,
                        ]);

                        ProductAttributeValue::create([
                            'product_variant_id' => $variant->id,
                            'attribute_id' => $colorAttr->id,
                            'attribute_option_id' => $colorOption->id,
                        ]);

                        ProductAttributeValue::create([
                            'product_variant_id' => $variant->id,
                            'attribute_id' => $materialAttr->id,
                            'attribute_option_id' => $materialOption->id,
                        ]);

                        $variantIndex++;
                    }
                }
            }
        }
    }

    private function generateSku(Category $category, int $productId, int $variantIndex): string
    {
        $categoryCode = substr(strtoupper($category->name), 0, 3);
        $productPart = str_pad((string) $productId, 4, '0', STR_PAD_LEFT);
        $variantPart = str_pad((string) $variantIndex, 3, '0', STR_PAD_LEFT);

        return "{$categoryCode}-{$productPart}-{$variantPart}";
    }
}
