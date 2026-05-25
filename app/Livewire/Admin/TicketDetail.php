<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Domains\Support\Models\Ticket;
use App\Domains\Support\Services\TicketService;
use App\Enums\TicketStatus;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class TicketDetail extends Component
{
    public Ticket $ticket;
    public string $newMessage = '';
    public bool $isInternal = false;
    public ?int $selectedStaffId = null;
    public ?TicketStatus $selectedStatus = null;

    protected TicketService $ticketService;

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
        $this->ticketService = app(TicketService::class);

        // Vérifier que l'utilisateur a permission support.manage
        if (!Auth::user()->can('support.manage')) {
            abort(403);
        }
    }

    public function render()
    {
        $staffMembers = User::role('staff')
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.admin.ticket-detail', [
            'messages' => $this->ticket->messages()->get(),
            'staffMembers' => $staffMembers,
            'statuses' => TicketStatus::cases(),
        ]);
    }

    public function reply(): void
    {
        $this->validate([
            'newMessage' => 'required|string|min:5|max:5000',
        ]);

        $this->ticketService->reply($this->ticket, Auth::user(), $this->newMessage, $this->isInternal);

        $this->newMessage = '';
        $this->isInternal = false;
        $this->ticket->refresh();

        $this->dispatch('reply-added');
    }

    public function changeStatus(TicketStatus $status): void
    {
        $this->ticketService->changeStatus($this->ticket, $status);
        $this->ticket->refresh();

        $this->dispatch('status-changed');
    }

    public function assignTo(?int $staffId): void
    {
        if ($staffId === null) {
            $staff = null;
        } else {
            $staff = User::findOrFail($staffId);
        }

        $this->ticketService->assign($this->ticket, $staff);
        $this->ticket->refresh();
        $this->selectedStaffId = null;

        $this->dispatch('assigned');
    }

    public function reassign(): void
    {
        $this->ticketService->assignAutomatic($this->ticket);
        $this->ticket->refresh();

        $this->dispatch('reassigned');
    }
}
