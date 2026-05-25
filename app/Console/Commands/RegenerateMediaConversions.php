<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class RegenerateMediaConversions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'media:regenerate {--collection= : Filter by collection name} {--model= : Filter by model class}';

    /**
     * The console command description.
     */
    protected $description = 'Regenerate media conversions for all media files or filtered by collection/model';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $collection = $this->option('collection');
        $model = $this->option('model');

        $query = Media::query();

        if ($collection) {
            $query->where('collection_name', $collection);
        }

        if ($model) {
            $query->where('model_type', $model);
        }

        $media = $query->get();

        if ($media->isEmpty()) {
            $this->info('Aucun média trouvé à régénérer.');
            return self::SUCCESS;
        }

        $this->info("Régénération de {$media->count()} fichier(s) média...");

        $bar = $this->output->createProgressBar($media->count());
        $bar->start();

        foreach ($media as $item) {
            try {
                $item->model->refresh();
                $item->refresh();
                $bar->advance();
            } catch (\Exception $e) {
                $bar->advance();
                $this->error("Erreur lors de la régénération du média ID {$item->id}: {$e->getMessage()}");
            }
        }

        $bar->finish();

        $this->newLine();
        $this->info('✓ Conversions régénérées avec succès.');

        return self::SUCCESS;
    }
}
