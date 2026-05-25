<?php

declare(strict_types=1);

namespace App\Domains\Support\Services;

use App\Domains\Support\Models\Ticket;
use App\Domains\Support\Models\TicketMessage;
use App\Domains\Support\Events\TicketReplied;
use App\Enums\TicketStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class TicketService
{
    /**
     * Crée un nouveau ticket.
     */
    public function createTicket(User $user, array $data): Ticket
    {
        $ticket = Ticket::create([
            'user_id' => $user->id,
            'category' => $data['category'],
            'priority' => $data['priority'] ?? 'medium',
            'status' => TicketStatus::OPEN,
            'subject' => $data['subject'],
            'description' => $data['description'],
            'order_id' => $data['order_id'] ?? null,
        ]);

        // Première réponse du client (la description)
        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'content' => $data['description'],
            'is_internal' => false,
        ]);

        // Assignation automatique round-robin
        $this->assignAutomatic($ticket);

        return $ticket->refresh();
    }

    /**
     * Ajoute une réponse au ticket.
     */
    public function reply(Ticket $ticket, User $user, string $content, bool $internal = false): TicketMessage
    {
        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'content' => $content,
            'is_internal' => $internal,
        ]);

        // Si c'est une réponse du client à "En attente du client", marquer comme "En cours"
        if ($user->id === $ticket->user_id && $ticket->status === TicketStatus::WAITING_CUSTOMER) {
            $this->changeStatus($ticket, TicketStatus::IN_PROGRESS);
        }

        // Si c'est une réponse du staff et que le ticket est "Ouvert", passer à "En cours"
        if ($user->id !== $ticket->user_id && $ticket->status === TicketStatus::OPEN) {
            $this->changeStatus($ticket, TicketStatus::IN_PROGRESS);
        }

        // Dispatcher l'événement
        TicketReplied::dispatch($message);

        return $message;
    }

    /**
     * Assigne un ticket à un staff member (auto round-robin si null).
     */
    public function assign(Ticket $ticket, ?User $staff = null): Ticket
    {
        if ($staff === null) {
            $staff = $this->getNextStaffForAssignment();
        }

        if ($staff) {
            $ticket->update(['assigned_to' => $staff->id]);
        }

        return $ticket->refresh();
    }

    /**
     * Assignation automatique round-robin.
     */
    public function assignAutomatic(Ticket $ticket): Ticket
    {
        $staff = $this->getNextStaffForAssignment();

        if ($staff) {
            $ticket->update(['assigned_to' => $staff->id]);
        }

        return $ticket->refresh();
    }

    /**
     * Obtient le prochain staff pour assignation (round-robin).
     */
    public function getNextStaffForAssignment(): ?User
    {
        // Récupère tous les staff avec permission support.manage
        $staffMembers = User::role('staff')
            ->where('active', true)
            ->orderBy('id')
            ->get();

        if ($staffMembers->isEmpty()) {
            return null;
        }

        // Compte les tickets assignés à chacun
        $staffWithCounts = $staffMembers->map(function (User $staff) {
            return [
                'user' => $staff,
                'count' => Ticket::where('assigned_to', $staff->id)
                    ->open()
                    ->count(),
            ];
        });

        // Retourne le staff avec le moins de tickets
        return collect($staffWithCounts)
            ->sortBy('count')
            ->first()['user'] ?? null;
    }

    /**
     * Change le statut du ticket.
     */
    public function changeStatus(Ticket $ticket, TicketStatus $status): Ticket
    {
        $ticket->update(['status' => $status]);

        return $ticket->refresh();
    }

    /**
     * Ferme un ticket résolu (après 30 jours).
     */
    public function closeResolved(): void
    {
        Ticket::where('status', TicketStatus::RESOLVED)
            ->where('updated_at', '<', now()->subDays(30))
            ->update(['status' => TicketStatus::CLOSED->value]);
    }

    /**
     * Récupère les tickets non assignés.
     */
    public function getUnassignedTickets(): Collection
    {
        return Ticket::unassigned()
            ->open()
            ->orderByPriority()
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Récupère les tickets assignés à un staff.
     */
    public function getAssignedTickets(User $staff): Collection
    {
        return Ticket::assignedTo($staff)
            ->open()
            ->orderByPriority()
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Compte les tickets SLA dépassés.
     */
    public function getOverdueTicketsCount(): int
    {
        return Ticket::open()
            ->get()
            ->filter(fn (Ticket $ticket) => $ticket->isOverdue())
            ->count();
    }

    /**
     * Obtient les statistiques des tickets.
     */
    public function getStats(): array
    {
        return [
            'total' => Ticket::count(),
            'open' => Ticket::open()->count(),
            'closed' => Ticket::closed()->count(),
            'overdue' => $this->getOverdueTicketsCount(),
            'unassigned' => Ticket::unassigned()->open()->count(),
            'by_priority' => [
                'critical' => Ticket::byPriority(\App\Enums\TicketPriority::CRITICAL)->open()->count(),
                'high' => Ticket::byPriority(\App\Enums\TicketPriority::HIGH)->open()->count(),
                'medium' => Ticket::byPriority(\App\Enums\TicketPriority::MEDIUM)->open()->count(),
                'low' => Ticket::byPriority(\App\Enums\TicketPriority::LOW)->open()->count(),
            ],
            'by_status' => [
                'open' => Ticket::byStatus(TicketStatus::OPEN)->count(),
                'in_progress' => Ticket::byStatus(TicketStatus::IN_PROGRESS)->count(),
                'waiting_customer' => Ticket::byStatus(TicketStatus::WAITING_CUSTOMER)->count(),
                'resolved' => Ticket::byStatus(TicketStatus::RESOLVED)->count(),
                'closed' => Ticket::byStatus(TicketStatus::CLOSED)->count(),
            ],
        ];
    }
}
