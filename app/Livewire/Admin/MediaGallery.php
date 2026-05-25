<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaGallery extends Component
{
    public Model $model;

    public string $collectionName = 'images';

    public string $title = 'Galerie d\'images';

    public array $mediaItems = [];

    public ?int $primaryImageId = null;

    public function mount()
    {
        $this->loadMediaItems();
    }

    public function loadMediaItems()
    {
        $this->mediaItems = $this->model->getMedia($this->collectionName)
            ->map(fn ($media) => [
                'id' => $media->id,
                'name' => $media->name,
                'url' => $media->getUrl('thumb'),
                'original_url' => $media->getUrl(),
                'order' => $media->order_column,
            ])
            ->toArray();
    }

    public function reorderImages($mediaIds)
    {
        foreach ($mediaIds as $order => $mediaId) {
            Media::where('id', $mediaId)->update(['order_column' => $order]);
        }

        $this->loadMediaItems();
        $this->dispatch('notification', 'Images réorganisées');
    }

    public function deleteImage($mediaId)
    {
        try {
            Media::find($mediaId)?->delete();
            $this->loadMediaItems();
            $this->dispatch('notification', 'Image supprimée');
            $this->dispatch('media:deleted');
        } catch (\Exception $e) {
            $this->dispatch('notification', "Erreur : {$e->getMessage()}");
        }
    }

    public function setPrimary($mediaId)
    {
        $this->primaryImageId = $mediaId;
        $this->dispatch('notification', 'Image principale définie');
    }

    public function render()
    {
        return view('livewire.admin.media-gallery', [
            'imageCount' => count($this->mediaItems),
        ]);
    }
}
