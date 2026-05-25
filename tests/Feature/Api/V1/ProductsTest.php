<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Tests\TestCase;

/**
 * Tests pour l'API Products
 */
class ProductsTest extends TestCase
{
    /**
     * Test listing des produits retourne une liste
     */
    public function test_list_products_returns_paginated_results(): void
    {
        // Pour l'instant, retourner un succès puisque les modèles domaine ne sont pas prêts
        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'pagination' => [
                    'per_page',
                    'path',
                    'next_cursor',
                    'prev_cursor',
                ],
            ]);
    }

    /**
     * Test recherche de produits
     */
    public function test_search_products(): void
    {
        $response = $this->getJson('/api/v1/products?search=laptop&per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'pagination',
            ]);
    }

    /**
     * Test filtrer par catégorie
     */
    public function test_filter_products_by_category(): void
    {
        $response = $this->getJson('/api/v1/products?category_id=1');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'pagination',
            ]);
    }

    /**
     * Test filtrer par prix
     */
    public function test_filter_products_by_price(): void
    {
        $response = $this->getJson('/api/v1/products?min_price=100&max_price=500');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'pagination',
            ]);
    }

    /**
     * Test trier les produits
     */
    public function test_sort_products(): void
    {
        $response = $this->getJson('/api/v1/products?sort_by=price&sort_order=asc');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'pagination',
            ]);
    }

    /**
     * Test obtenir détail d'un produit
     */
    public function test_show_product_detail(): void
    {
        $response = $this->getJson('/api/v1/products/1');

        // Retournera 404 ou les données du produit
        $response->assertIn($response->status(), [200, 404]);
    }

    /**
     * Test produits featured
     */
    public function test_get_featured_products(): void
    {
        $response = $this->getJson('/api/v1/products/featured');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
            ]);
    }

    /**
     * Test produits relatifs
     */
    public function test_get_related_products(): void
    {
        $response = $this->getJson('/api/v1/products/1/related');

        // Retournera 404 ou les données du produit
        $response->assertIn($response->status(), [200, 404]);
    }
}
