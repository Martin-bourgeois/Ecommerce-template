<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Domains\Support\Models\Ticket;
use App\Domains\Support\Services\TicketService;
use App\Enums\TicketPriority;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class TicketQueue extends Component
{
    use WithPagination;

    public string $filter = 'assigned_to_me'; // assigned_to_me, unassigned, all
    protected TicketService $ticketService;

    public function mount(): void
    {
        $this->ticketService = app(TicketService::class);
    }

    public function render()
    {
        $query = Ticket::open();

        if ($this->filter === 'assigned_to_me') {
            $query->assignedTo(Auth::user());
        } elseif ($this->filter === 'unassigned') {
            $query->unassigned();
        }
        // else: all

        $tickets = $query->orderByPriority()
            ->orderByDesc('created_at')
            ->with(['client', 'assignedTo'])
            ->paginate(15);

        return view('livewire.admin.ticket-queue', [
            'tickets' => $tickets,
            'stats' => $this->ticketService->getStats(),
        ]);
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
        $this->resetPage();
    }
}
