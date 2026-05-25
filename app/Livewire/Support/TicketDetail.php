<?php

declare(strict_types=1);

namespace App\Livewire\Support;

use App\Domains\Support\Models\Ticket;
use App\Domains\Support\Services\TicketService;
use App\Enums\TicketStatus;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class TicketDetail extends Component
{
    public Ticket $ticket;
    public string $newMessage = '';
    public bool $showNewStatus = false;
    public ?TicketStatus $selectedStatus = null;

    protected TicketService $ticketService;

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
        $this->ticketService = app(TicketService::class);

        // Vérifier que l'utilisateur a accès au ticket
        $user = Auth::user();
        if ($user->id !== $ticket->user_id && $user->id !== $ticket->assigned_to && !$user->can('support.manage')) {
            abort(403);
        }
    }

    public function render()
    {
        return view('livewire.support.ticket-detail', [
            'messages' => $this->ticket->messages()->get(),
            'statuses' => TicketStatus::cases(),
        ]);
    }

    public function reply(): void
    {
        $this->validate([
            'newMessage' => 'required|string|min:5|max:5000',
        ]);

        $this->ticketService->reply($this->ticket, Auth::user(), $this->newMessage);

        $this->newMessage = '';
        $this->ticket->refresh();

        $this->dispatch('reply-added');
    }

    public function changeStatus(TicketStatus $status): void
    {
        if (!Auth::user()->can('support.manage')) {
            abort(403);
        }

        $this->ticketService->changeStatus($this->ticket, $status);
        $this->ticket->refresh();

        $this->dispatch('status-changed');
    }
}
