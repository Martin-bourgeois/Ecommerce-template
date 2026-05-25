<?php

declare(strict_types=1);

namespace App\Livewire\Account;

use App\Domains\Order\Models\Order;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class OrderDetail extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        // Verify user owns this order
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $this->order = $order->load('items', 'shippingAddress', 'billingAddress', 'statusHistories.user', 'payment');
    }

    public function render()
    {
        return view('livewire.account.order-detail');
    }

    public function canCancel(): bool
    {
        return $this->order->canBeCancelled();
    }

    public function cancel(): void
    {
        if (!$this->canCancel()) {
            $this->dispatch('notification', type: 'error', message: 'Cette commande ne peut pas être annulée');
            return;
        }

        try {
            $orderService = app(\App\Domains\Order\Services\OrderService::class);
            $orderService->cancel($this->order, Auth::user(), 'Cancelled by customer');

            $this->order->refresh();
            $this->dispatch('notification', type: 'success', message: 'Commande annulée');
        } catch (\Exception $e) {
            $this->dispatch('notification', type: 'error', message: $e->getMessage());
        }
    }
}
