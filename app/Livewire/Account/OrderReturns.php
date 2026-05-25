<?php

declare(strict_types=1);

namespace App\Livewire\Account;

use App\Domains\Returns\Models\Rma;
use App\Domains\Returns\Services\RmaService;
use Livewire\Component;
use Livewire\WithPagination;

class OrderReturns extends Component
{
    use WithPagination;

    protected RmaService $rmaService;

    public function mount(): void
    {
        $this->rmaService = app(RmaService::class);
    }

    public function render()
    {
        $rmas = Rma::where('user_id', auth()->id())
            ->with(['order', 'items'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('livewire.account.order-returns', [
            'rmas' => $rmas,
        ]);
    }
}
