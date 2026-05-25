<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Domains\Returns\Models\Rma;
use App\Domains\Returns\Services\RmaService;
use App\Enums\RmaStatus;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class RmaManagement extends Component
{
    use WithPagination;

    protected RmaService $rmaService;
    public string $activeTab = 'pending';
    public string $selectedRmaId = '';
    public array $itemConditions = [];

    public function mount(): void
    {
        $this->rmaService = app(RmaService::class);
    }

    public function approveRma(Rma $rma): void
    {
        try {
            $this->rmaService->approve($rma, Auth::user());
            $this->dispatch('notify', type: 'success', message: 'RMA approuvé avec succès');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function receiveRma(Rma $rma): void
    {
        try {
            $this->rmaService->receiveItems($rma);
            $this->dispatch('notify', type: 'success', message: 'Articles réceptionnés');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function inspectRma(Rma $rma): void
    {
        if (empty($this->itemConditions)) {
            $this->dispatch('notify', type: 'error', message: 'Veuillez spécifier la condition de tous les articles');
            return;
        }

        try {
            $this->rmaService->inspectItems($rma, $this->itemConditions);
            $this->itemConditions = [];
            $this->dispatch('notify', type: 'success', message: 'Articles inspectés');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function refundRma(Rma $rma): void
    {
        try {
            $this->rmaService->processRefund($rma, Auth::user());
            $this->dispatch('notify', type: 'success', message: 'Remboursement traité');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function rejectRma(Rma $rma, string $reason): void
    {
        try {
            $this->rmaService->reject($rma, $reason);
            $this->dispatch('notify', type: 'success', message: 'RMA rejeté');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    public function render()
    {
        $rmas = match ($this->activeTab) {
            'pending' => $this->rmaService->getPendingRmas(),
            'receipt' => $this->rmaService->getAwaitingReceiptRmas(),
            'inspection' => $this->rmaService->getAwaitingInspectionRmas(),
            default => collect(),
        };

        return view('livewire.admin.rma-management', [
            'rmas' => $rmas,
            'stats' => $this->rmaService->getStats(),
        ]);
    }
}
