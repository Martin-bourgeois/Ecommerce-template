<?php

declare(strict_types=1);

namespace App\Livewire\Account;

use App\Domains\Order\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class OrderList extends Component
{
    use WithPagination;

    public int $perPage = 10;
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';

    public function render()
    {
        $orders = Order::where('user_id', auth()->id())
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.account.order-list', [
            'orders' => $orders,
        ]);
    }

    public function sortBy(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'desc';
        }
        $this->resetPage();
    }
}
