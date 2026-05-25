<?php

declare(strict_types=1);

namespace App\Livewire\Account;

use App\Domains\Returns\Models\Rma;
use Livewire\Component;

class RmaDetail extends Component
{
    public Rma $rma;

    public function mount(Rma $rma): void
    {
        $this->rma = $rma;

        // Vérifier l'accès
        if ($rma->user_id !== auth()->id()) {
            abort(403);
        }

        $this->rma->load(['order', 'items.orderItem.product']);
    }

    public function render()
    {
        return view('livewire.account.rma-detail', [
            'rma' => $this->rma,
        ]);
    }
}
