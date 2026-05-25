<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Catalog\Models\Product;
use Illuminate\Console\Command;
use Laravel\Scout\Searchable;
use MeiliSearch\Meilisearch;

class SyncSearchIndex extends Command
{
    protected $signature = 'scout:sync-index
                            {--model=App\\Domains\\Catalog\\Models\\Product : Model to index}
                            {--rebuild : Rebuild the entire index}';

    protected $description = 'Sync searchable models to Meilisearch and configure settings';

    public function handle()
    {
        $modelClass = $this->option('model');
        $rebuild = $this->option('rebuild');

        if (!class_exists($modelClass)) {
            $this->error("Model class {$modelClass} not found.");
            return 1;
        }

        $this->info("Syncing {$modelClass} to Meilisearch...");

        try {
            // Rebuild index if requested
            if ($rebuild) {
                $this->rebuildIndex($modelClass);
            } else {
                // Regular sync
                $this->syncIndex($modelClass);
            }

            // Configure Meilisearch settings
            $this->configureSettings();

            $this->info('✓ Index synced and configured successfully');
            return 0;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }

    private function syncIndex(string $modelClass): void
    {
        $model = new $modelClass();

        if (!in_array(Searchable::class, class_uses_recursive($model))) {
            $this->error("Model {$modelClass} does not use Searchable trait.");
            return;
        }

        $count = $modelClass::whereNull('deleted_at')
            ->searchable();

        $this->line("Queued {$count} models for indexing");
    }

    private function rebuildIndex(string $modelClass): void
    {
        $model = new $modelClass();

        if (!in_array(Searchable::class, class_uses_recursive($model))) {
            $this->error("Model {$modelClass} does not use Searchable trait.");
            return;
        }

        $this->info('Dropping existing index...');
        $model->searchableUsing()->deleteIndex(
            $model->searchableAs()
        );

        $this->info('Creating new index...');
        $count = $modelClass::whereNull('deleted_at')
            ->searchable();

        $this->line("Synced {$count} models");
    }

    private function configureSettings(): void
    {
        try {
            $client = app('meilisearch.client') ?? new Meilisearch(
                config('scout.meilisearch.host'),
                config('scout.meilisearch.key')
            );

            $settings = config('scout.meilisearch.index-settings.products', []);

            if (!empty($settings)) {
                $index = $client->index('products');

                if (isset($settings['filterableAttributes'])) {
                    $index->updateFilterableAttributes(
                        $settings['filterableAttributes']
                    );
                    $this->line('✓ Filterable attributes configured');
                }

                if (isset($settings['searchableAttributes'])) {
                    $index->updateSearchableAttributes(
                        $settings['searchableAttributes']
                    );
                    $this->line('✓ Searchable attributes configured');
                }

                if (isset($settings['sortableAttributes'])) {
                    $index->updateSortableAttributes(
                        $settings['sortableAttributes']
                    );
                    $this->line('✓ Sortable attributes configured');
                }

                if (isset($settings['rankingRules'])) {
                    $index->updateRankingRules(
                        $settings['rankingRules']
                    );
                    $this->line('✓ Ranking rules configured');
                }

                if (isset($settings['displayedAttributes'])) {
                    $index->updateDisplayedAttributes(
                        $settings['displayedAttributes']
                    );
                    $this->line('✓ Displayed attributes configured');
                }
            }
        } catch (\Exception $e) {
            $this->warn('Could not configure Meilisearch settings: ' . $e->getMessage());
        }
    }
}
