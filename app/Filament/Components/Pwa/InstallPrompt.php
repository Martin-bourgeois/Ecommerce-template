<?php

declare(strict_types=1);

namespace App\Filament\Components\Pwa;

use Livewire\Component;
use Illuminate\Support\Facades\Session;

class InstallPrompt extends Component
{
    public bool $showPrompt = false;
    public bool $isInstallable = false;

    public function mount(): void
    {
        // La détection est faite côté JavaScript
    }

    public function dismissPrompt(): void
    {
        $this->showPrompt = false;
        // Marquer comme rejeté pour 30 jours
        Session::put('pwa_install_dismissed', now()->addDays(30));
    }

    public function installApp(): void
    {
        $this->dispatch('pwa:install');
    }

    public function render()
    {
        return view('livewire.pwa.install-prompt');
    }
}
