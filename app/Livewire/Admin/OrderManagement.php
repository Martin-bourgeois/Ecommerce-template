<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Domains\Order\Enums\OrderStatus;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Services\OrderService;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class OrderManagement extends Component
{
    use WithPagination;

    public int $perPage = 20;
    public string $statusFilter = '';
    public string $searchQuery = '';
    public ?int $editingOrderId = null;
    public OrderStatus $newStatus;
    public string $statusReason = '';

    public function render()
    {
        $query = Order::query();

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->searchQuery) {
            $query->where('order_number', 'like', "%{$this->searchQuery}%")
                ->orWhereHas('user', fn ($q) => $q->where('email', 'like', "%{$this->searchQuery}%"));
        }

        $orders = $query->latest('created_at')->paginate($this->perPage);

        return view('livewire.admin.order-management', [
            'orders' => $orders,
            'orderStatuses' => OrderStatus::cases(),
        ]);
    }

    public function editOrder(int $orderId): void
    {
        $this->editingOrderId = $orderId;
        $order = Order::findOrFail($orderId);
        $this->newStatus = $order->status;
        $this->statusReason = '';
    }

    public function closeEdit(): void
    {
        $this->editingOrderId = null;
        $this->statusReason = '';
    }

    public function updateStatus(): void
    {
        $order = Order::findOrFail($this->editingOrderId);

        try {
            $service = app(OrderService::class);
            $service->transitionStatus(
                $order,
                $this->newStatus,
                Auth::user(),
                $this->statusReason ?: null,
            );

            $this->dispatch('notification', type: 'success', message: "Statut mis à jour");
            $this->closeEdit();
            $this->resetPage();
        } catch (\Exception $e) {
            $this->dispatch('notification', type: 'error', message: $e->getMessage());
        }
    }

    public function cancelOrder(int $orderId): void
    {
        $order = Order::findOrFail($orderId);

        try {
            $service = app(OrderService::class);
            $service->cancel($order, Auth::user(), 'Cancelled by admin');

            $this->dispatch('notification', type: 'success', message: "Commande annulée");
            $this->resetPage();
        } catch (\Exception $e) {
            $this->dispatch('notification', type: 'error', message: $e->getMessage());
        }
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSearchQuery(): void
    {
        $this->resetPage();
    }
}
