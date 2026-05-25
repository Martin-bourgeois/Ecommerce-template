<?php

return [
    /*
     * Driver to use for searching
     */
    'driver' => env('SCOUT_DRIVER', 'meilisearch'),

    /*
     * Meilisearch configuration
     */
    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY', null),
        'index-settings' => [
            'products' => [
                'filterableAttributes' => [
                    'category_id',
                    'category_name',
                    'price',
                    'in_stock',
                    'rating_avg',
                    'attributes',
                ],
                'searchableAttributes' => [
                    'name',
                    'description',
                    'sku',
                    'category_name',
                    'brand',
                ],
                'sortableAttributes' => [
                    'price',
                    'created_at',
                    'rating_avg',
                    'popularity',
                ],
                'rankingRules' => [
                    'sort',
                    'words',
                    'typo',
                    'proximity',
                    'attribute',
                    'exactness',
                ],
                'displayedAttributes' => [
                    'id',
                    'name',
                    'slug',
                    'description',
                    'sku',
                    'category_id',
                    'category_name',
                    'price',
                    'in_stock',
                    'rating_avg',
                    'popularity',
                    'created_at',
                ],
                'pagination' => [
                    'maxTotalHits' => 10000,
                ],
            ],
        ],
    ],

    /*
     * Queue configuration
     */
    'queue' => true,
    'batch' => [
        'size' => 500,
    ],

    /*
     * Model prefix for index naming
     */
    'prefix' => env('SCOUT_PREFIX', ''),

    /*
     * Soft delete flag
     */
    'soft_delete' => false,
];
