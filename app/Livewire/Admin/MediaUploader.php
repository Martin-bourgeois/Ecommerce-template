<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Database\Eloquent\Model;

class MediaUploader extends Component
{
    use WithFileUploads;

    public Model $model;

    public string $collectionName = 'images';

    public string $title = 'Uploader des images';

    public int $maxFiles = 10;

    public int $maxSizeMB = 5;

    public array $uploadedFiles = [];

    public array $previews = [];

    public bool $isUploading = false;

    public string $uploadProgress = '0%';

    protected $rules = [
        'uploadedFiles.*' => 'required|image|mimes:jpeg,png,webp|max:5242880', // 5MB
    ];

    protected $messages = [
        'uploadedFiles.*.required' => 'Le fichier est requis.',
        'uploadedFiles.*.image' => 'Le fichier doit être une image.',
        'uploadedFiles.*.mimes' => 'Les formats autorisés sont : JPEG, PNG, WebP.',
        'uploadedFiles.*.max' => 'La taille maximale est 5 MB.',
    ];

    public function updatedUploadedFiles()
    {
        // Generate previews
        foreach ($this->uploadedFiles as $key => $file) {
            if ($file) {
                $this->previews[$key] = $file->temporaryUrl();
            }
        }

        $this->validate();
    }

    public function upload()
    {
        if (count($this->uploadedFiles) === 0) {
            $this->dispatch('notification', 'Veuillez sélectionner au moins une image');
            return;
        }

        $this->isUploading = true;

        foreach ($this->uploadedFiles as $file) {
            try {
                $this->model->addMedia($file)
                    ->toMediaCollection($this->collectionName);
            } catch (\Exception $e) {
                $this->dispatch('notification', "Erreur lors du téléchargement : {$e->getMessage()}");
                $this->isUploading = false;
                return;
            }
        }

        $this->isUploading = false;
        $this->uploadedFiles = [];
        $this->previews = [];

        $this->dispatch('notification', 'Images téléchargées avec succès');
        $this->dispatch('media:uploaded');
    }

    public function removePreview($key)
    {
        unset($this->uploadedFiles[$key]);
        unset($this->previews[$key]);

        $this->uploadedFiles = array_values($this->uploadedFiles);
        $this->previews = array_values($this->previews);
    }

    public function render()
    {
        return view('livewire.admin.media-uploader', [
            'fileCount' => count($this->uploadedFiles),
            'remainingSlots' => $this->maxFiles - count($this->uploadedFiles),
        ]);
    }
}
