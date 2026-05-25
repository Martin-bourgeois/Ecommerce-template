<?php

declare(strict_types=1);

namespace App\Http\Livewire\Account;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class OrdersList extends Component
{
    use WithPagination;

    #[Url]
    public string $filter = 'all';

    public function render()
    {
        $user = Auth::user();
        
        $query = $user->orders();
        
        if ($this->filter === 'pending') {
            $query->where('status', '!=', 'completed');
        } elseif ($this->filter === 'completed') {
            $query->where('status', 'completed');
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.account.orders-list', [
            'orders' => $orders,
        ]);
    }
}
