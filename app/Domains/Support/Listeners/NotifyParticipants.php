<?php

declare(strict_types=1);

namespace App\Domains\Support\Listeners;

use App\Domains\Support\Events\TicketReplied;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyParticipants implements ShouldQueue
{
    public function handle(TicketReplied $event): void
    {
        $message = $event->message;
        $ticket = $message->ticket;
        $author = $message->author;

        // Récupère les participants (client + staff assigné)
        $recipients = collect();

        // Ajoute le client si le message vient du staff
        if ($message->isFromStaff()) {
            $recipients->push($ticket->client);
        }

        // Ajoute le staff si le message vient du client ET qu'il y a un staff assigné
        if ($message->isFromClient() && $ticket->assignedTo) {
            $recipients->push($ticket->assignedTo);
        }

        // Envoie les notifications (sauf à l'auteur du message)
        foreach ($recipients as $recipient) {
            if ($recipient->id !== $author->id) {
                try {
                    Mail::to($recipient)->send(new TicketReplyNotification($message));
                } catch (\Exception $e) {
                    Log::error("Failed to send ticket notification: " . $e->getMessage());
                }
            }
        }
    }
}

/**
 * Mailable pour la notification de réponse
 */
class TicketReplyNotification extends Mailable
{
    public function __construct(
        public $message
    ) {
    }

    public function envelope()
    {
        $ticket = $this->message->ticket;

        return [
            'subject' => "Nouvelle réponse au ticket #{$ticket->id}: {$ticket->subject}",
        ];
    }

    public function content()
    {
        return [
            'view' => 'emails.support.ticket-reply',
            'with' => [
                'message' => $this->message,
                'ticket' => $this->message->ticket,
                'author' => $this->message->author,
            ],
        ];
    }
}
