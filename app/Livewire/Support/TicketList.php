<?php

declare(strict_types=1);

namespace App\Livewire\Support;

use App\Domains\Support\Models\Ticket;
use App\Enums\TicketStatus;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class TicketList extends Component
{
    use WithPagination;

    public ?TicketStatus $filterStatus = null;

    public function render()
    {
        $query = Auth::user()->tickets()->with(['assignedTo', 'messages']);

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        $tickets = $query->orderByDesc('created_at')->paginate(10);

        return view('livewire.support.ticket-list', [
            'tickets' => $tickets,
            'statuses' => TicketStatus::cases(),
        ]);
    }

    public function setFilter(?TicketStatus $status): void
    {
        $this->filterStatus = $status;
        $this->resetPage();
    }
}
