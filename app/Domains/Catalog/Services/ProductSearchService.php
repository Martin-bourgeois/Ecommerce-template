<?php

declare(strict_types=1);

namespace App\Domains\Catalog\Services;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Filters\CategoryFilter;
use App\Domains\Catalog\Filters\PriceRangeFilter;
use App\Domains\Catalog\Filters\AttributeFilter;
use App\Domains\Catalog\Filters\AvailabilityFilter;
use App\Domains\Catalog\Filters\RatingFilter;
use App\Domains\Catalog\Sorting\ProductSort;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Laravel\Scout\Builder as ScoutBuilder;

class ProductSearchService
{
    private const CACHE_TTL = 300; // 5 minutes
    private const CACHE_PREFIX = 'product_search:';
    private const PER_PAGE = 24;

    public function search(
        ?string $query = null,
        array $filters = [],
        ?string $sort = null,
        int $perPage = self::PER_PAGE,
        int $page = 1
    ) {
        // Build cache key
        $cacheKey = $this->buildCacheKey($query, $filters, $sort, $perPage, $page);

        // Try cache first
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        // Try Meilisearch search first
        try {
            $results = $this->searchWithMeilisearch($query, $filters, $sort, $perPage, $page);
        } catch (\Exception $e) {
            Log::warning('Meilisearch search failed, falling back to database', [
                'error' => $e->getMessage(),
            ]);
            $results = $this->searchWithDatabase($query, $filters, $sort, $perPage, $page);
        }

        // Cache results
        Cache::put($cacheKey, $results, self::CACHE_TTL);

        return $results;
    }

    /**
     * Search using Meilisearch
     */
    private function searchWithMeilisearch(
        ?string $query,
        array $filters,
        ?string $sort,
        int $perPage,
        int $page
    ) {
        $scoutQuery = Product::query();

        // Full-text search
        if ($query) {
            $scoutQuery = $scoutQuery->search($query);
        }

        // Apply Meilisearch filters
        $meilisearchFilters = $this->buildMeilisearchFilters($filters);
        if (!empty($meilisearchFilters)) {
            $scoutQuery = $scoutQuery->where($meilisearchFilters);
        }

        // Meilisearch handles sorting
        if ($sort && $sort !== 'relevance') {
            $scoutQuery = $scoutQuery->orderBy(
                $this->getMeilisearchSortField($sort),
                $this->getMeilisearchSortDirection($sort)
            );
        }

        // Paginate
        $results = $scoutQuery->paginate($perPage, 'page', $page);

        return $this->formatResults($results, $query, $filters, $sort);
    }

    /**
     * Fallback: search using database
     */
    private function searchWithDatabase(
        ?string $query,
        array $filters,
        ?string $sort,
        int $perPage,
        int $page
    ) {
        $dbQuery = Product::active();

        // Full-text search
        if ($query) {
            $dbQuery->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('sku', 'like', "%{$query}%");
            });
        }

        // Apply filters
        $dbQuery = $this->applyDatabaseFilters($dbQuery, $filters);

        // Apply sorting
        if ($sort) {
            $sortEnum = ProductSort::tryFrom($sort);
            if ($sortEnum) {
                $dbQuery = $sortEnum->apply($dbQuery);
            }
        }

        // Paginate
        $results = $dbQuery->paginate($perPage, ['*'], 'page', $page);

        return $this->formatResults($results, $query, $filters, $sort);
    }

    /**
     * Apply database filters using pipeline pattern
     */
    private function applyDatabaseFilters($query, array $filters)
    {
        // Category
        if (isset($filters['category'])) {
            $query = (new CategoryFilter())->apply($query, $filters['category']);
        }

        // Price range
        if (isset($filters['price'])) {
            $query = (new PriceRangeFilter())->apply($query, $filters['price']);
        }

        // Availability
        if (isset($filters['availability'])) {
            $query = (new AvailabilityFilter())->apply($query, $filters['availability']);
        }

        // Rating
        if (isset($filters['rating'])) {
            $query = (new RatingFilter())->apply($query, $filters['rating']);
        }

        // Attributes
        if (isset($filters['attributes'])) {
            $query = (new AttributeFilter())->apply($query, $filters['attributes']);
        }

        return $query;
    }

    /**
     * Build Meilisearch filter syntax
     */
    private function buildMeilisearchFilters(array $filters): array
    {
        $meilisearchFilters = [];

        if (isset($filters['category'])) {
            $categories = is_array($filters['category']) ? $filters['category'] : [$filters['category']];
            $meilisearchFilters['category_id'] = $categories;
        }

        if (isset($filters['price'])) {
            if (isset($filters['price']['min'])) {
                // Meilisearch range filter: price >= min
            }
            if (isset($filters['price']['max'])) {
                // Meilisearch range filter: price <= max
            }
        }

        if (isset($filters['availability'])) {
            $meilisearchFilters['in_stock'] = ($filters['availability'] !== 'out_of_stock');
        }

        return $meilisearchFilters;
    }

    /**
     * Get Meilisearch sort field
     */
    private function getMeilisearchSortField(string $sort): string
    {
        return match ($sort) {
            'price_asc', 'price_desc' => 'price',
            'newest' => 'created_at',
            'bestseller' => 'popularity',
            'rating' => 'rating_avg',
            default => 'created_at',
        };
    }

    /**
     * Get Meilisearch sort direction
     */
    private function getMeilisearchSortDirection(string $sort): string
    {
        return str_ends_with($sort, '_desc') || $sort === 'price_desc' ? 'desc' : 'asc';
    }

    /**
     * Format results for API response
     */
    private function formatResults($results, ?string $query, array $filters, ?string $sort)
    {
        return [
            'data' => $results->items(),
            'pagination' => [
                'total' => $results->total(),
                'per_page' => $results->perPage(),
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'from' => $results->firstItem(),
                'to' => $results->lastItem(),
            ],
            'meta' => [
                'query' => $query,
                'filters' => $filters,
                'sort' => $sort ?? 'relevance',
            ],
        ];
    }

    /**
     * Build cache key from search parameters
     */
    private function buildCacheKey(?string $query, array $filters, ?string $sort, int $perPage, int $page): string
    {
        $parts = [
            $query ?? 'all',
            json_encode($filters),
            $sort ?? 'relevance',
            $perPage,
            $page,
        ];

        return self::CACHE_PREFIX . md5(implode(':', $parts));
    }

    /**
     * Clear cache for given filters (called after indexing)
     */
    public function clearCache(array $filters = []): void
    {
        if (empty($filters)) {
            Cache::forget(self::CACHE_PREFIX . '*');
        } else {
            $pattern = self::CACHE_PREFIX . md5(json_encode($filters));
            Cache::forget($pattern);
        }
    }

    /**
     * Get available filter options
     */
    public function getFilterOptions(): array
    {
        return Cache::remember('product_filter_options', self::CACHE_TTL, function () {
            return [
                'categories' => $this->getCategories(),
                'price_range' => $this->getPriceRange(),
                'attributes' => $this->getFilterableAttributes(),
                'availability' => [
                    ['value' => 'all', 'label' => 'Tous les produits'],
                    ['value' => 'in_stock', 'label' => 'En stock'],
                    ['value' => 'out_of_stock', 'label' => 'Rupture de stock'],
                ],
                'ratings' => [
                    ['value' => 5, 'label' => '★★★★★ (5 stars)'],
                    ['value' => 4, 'label' => '★★★★☆ (4+ stars)'],
                    ['value' => 3, 'label' => '★★★☆☆ (3+ stars)'],
                    ['value' => 2, 'label' => '★★☆☆☆ (2+ stars)'],
                    ['value' => 1, 'label' => '★☆☆☆☆ (1+ stars)'],
                ],
            ];
        });
    }

    private function getCategories()
    {
        return \App\Domains\Catalog\Models\Category::active()
            ->get(['id', 'name'])
            ->map(fn($cat) => ['value' => $cat->id, 'label' => $cat->name])
            ->toArray();
    }

    private function getPriceRange()
    {
        $stats = Product::active()
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();

        return [
            'min' => $stats?->min_price ?? 0,
            'max' => $stats?->max_price ?? 1000,
        ];
    }

    private function getFilterableAttributes()
    {
        return \App\Domains\Catalog\Models\Attribute::filterable()
            ->with('options')
            ->get()
            ->map(fn($attr) => [
                'slug' => $attr->slug,
                'name' => $attr->name,
                'options' => $attr->options->map(fn($opt) => [
                    'value' => $opt->value,
                    'label' => $opt->label,
                ])->toArray(),
            ])
            ->toArray();
    }
}
