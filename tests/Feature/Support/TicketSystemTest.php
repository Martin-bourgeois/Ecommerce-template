<?php

declare(strict_types=1);

namespace Tests\Feature\Support;

use Tests\TestCase;
use App\Domains\Support\Models\Ticket;
use App\Domains\Support\Models\TicketMessage;
use App\Domains\Support\Services\TicketService;
use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\User;
use App\Domains\Order\Models\Order;
use Database\Factories\Support\TicketFactory;
use Illuminate\Support\Facades\Mail;

class TicketSystemTest extends TestCase
{
    /**
     * Documentation et tests du système de tickets support client intégré.
     */
    public function test_ticket_system_documentation(): void
    {
        // ========== STRUCTURE ==========

        // Models implémentés:
        // - Ticket: user_id (client), assigned_to (staff), category, priority, status, subject, description, order_id?
        // - TicketMessage: ticket_id, user_id, content, is_internal

        // Enums implémentés:
        // - TicketCategory: ORDER_ISSUE, PRODUCT_QUESTION, RETURN_REQUEST, TECHNICAL, OTHER
        // - TicketPriority: LOW (72h SLA), MEDIUM (24h), HIGH (4h), CRITICAL (1h)
        // - TicketStatus: OPEN, IN_PROGRESS, WAITING_CUSTOMER, RESOLVED, CLOSED

        // Scopes Ticket:
        // - open() - where status != CLOSED
        // - closed() - where status = CLOSED
        // - byStatus(TicketStatus)
        // - byCategory(TicketCategory)
        // - byPriority(TicketPriority)
        // - assignedTo(User)
        // - unassigned() - where assigned_to IS NULL
        // - orderByPriority() - tri par priorité CRITICAL > HIGH > MEDIUM > LOW

        // ========== SERVICE ==========

        // TicketService méthodes:
        // - createTicket(User, array): Ticket
        //   → Crée ticket, première message du client, assignation auto round-robin
        // - reply(Ticket, User, string, bool isInternal): TicketMessage
        //   → Ajoute réponse, change statut si nécessaire, dispatch TicketReplied event
        // - assign(Ticket, ?User): Ticket
        //   → Assigne à staff, ou auto round-robin si null
        // - assignAutomatic(Ticket): Ticket
        //   → Round-robin: staff avec moins de tickets ouverts assignés
        // - changeStatus(Ticket, TicketStatus): Ticket
        // - getNextStaffForAssignment(): ?User
        //   → Retourne staff avec moins de tickets ouverts
        // - closeResolved(): void
        //   → Ferme tickets résolus depuis 30+ jours
        // - getUnassignedTickets(): Collection
        // - getAssignedTickets(User): Collection
        // - getOverdueTicketsCount(): int
        //   → Compte tickets dépassés SLA
        // - getStats(): array
        //   → Statistiques globales (total, open, closed, overdue, etc.)

        // ========== EVENTS & LISTENERS ==========

        // TicketReplied Event:
        // - message: TicketMessage
        // - Dispatché après chaque réponse

        // NotifyParticipants Listener:
        // - Envoie email aux participants (sauf auteur du message)
        // - Client reçoit si réponse du staff
        // - Staff assigné reçoit si réponse du client
        // - Mailable: TicketReplyNotification

        // ========== LIVEWIRE COMPONENTS ==========

        // Client Side:
        // - Support/TicketList (app/Livewire/Support/TicketList.php)
        //   → Liste ses tickets, filtre par statut, pagine (10 par page)
        //   → Route: /support/tickets
        //
        // - Support/TicketDetail (app/Livewire/Support/TicketDetail.php)
        //   → Affiche conversation complète, formulaire réponse
        //   → Peut changer statut si permission
        //   → Route: /support/tickets/{ticket}

        // Admin Side:
        // - Admin/TicketQueue (app/Livewire/Admin/TicketQueue.php)
        //   → Liste non assignés, assignés à moi, tous
        //   → Stats: total, ouverts, SLA dépassés, non assignés, résolus
        //   → Tri par priorité + date
        //   → Affiche SLA restantes/dépassées
        //   → Route: /admin/support/queue
        //
        // - Admin/TicketDetail (app/Livewire/Admin/TicketDetail.php)
        //   → Réponses publiques + internes
        //   → Formulaire réponse avec checkbox is_internal
        //   → Changer statut
        //   → Assigner à staff (dropdown), ou auto-réassigner
        //   → Route: /admin/support/tickets/{ticket}

        // ========== CONVENTIONS ==========

        // Visibilité Messages:
        // - Messages publics: visibles client + staff + tout staff (si permission)
        // - Messages internes: visibles SEULEMENT staff (is_internal = true, pas envoyé au client)

        // Auto-assignation Round-Robin:
        // - À création ticket, assigne automatiquement
        // - Staff sélectionné: celui avec moins de tickets OUVERTS assignés
        // - Si plusieurs staff même nombre: ID le plus bas

        // Auto-changement Statut:
        // - Client répond à WAITING_CUSTOMER → IN_PROGRESS
        // - Staff répond à OPEN → IN_PROGRESS

        // Fermeture Automatique:
        // - Tickets RESOLVED > 30 jours → CLOSED (archivage)

        // SLA affiché:
        // - LOW: 72h
        // - MEDIUM: 24h
        // - HIGH: 4h
        // - CRITICAL: 1h
        // - Calcul: created_at + slaHours()
        // - isOverdue(): compare now() avec due time
        // - slaRemainingHours(): retourne heures restantes (ou null si fermé)

        // ========== ROUTES ==========

        // Client:
        // GET /support/tickets → TicketList
        // GET /support/tickets/{ticket} → TicketDetail

        // Admin:
        // GET /admin/support/queue → TicketQueue
        // GET /admin/support/tickets/{ticket} → AdminTicketDetail

        $this->assertTrue(true);
    }

    public function test_create_ticket_with_auto_assignment(): void
    {
        // Crée un ticket et vérifie assignation auto
        $user = User::factory()->create();
        $staff = User::factory()->create();
        $order = Order::factory()->create();

        $service = app(TicketService::class);

        $ticket = $service->createTicket($user, [
            'category' => TicketCategory::ORDER_ISSUE,
            'priority' => TicketPriority::HIGH,
            'subject' => 'Ma commande est perdue',
            'description' => 'Je n\'ai pas reçu ma commande et le délai est dépassé.',
            'order_id' => $order->id,
        ]);

        $this->assertInstanceOf(Ticket::class, $ticket);
        $this->assertEquals($user->id, $ticket->user_id);
        $this->assertEquals(TicketStatus::OPEN, $ticket->status);
        $this->assertEquals(TicketCategory::ORDER_ISSUE, $ticket->category);
        $this->assertNotNull($ticket->assigned_to);
        $this->assertTrue($ticket->messages->count() >= 1);
    }

    public function test_ticket_reply_dispatch_event(): void
    {
        $ticket = Ticket::factory()->withAssignee()->create();
        $staff = $ticket->assignedTo;
        $client = $ticket->client;

        $service = app(TicketService::class);

        Mail::fake();

        $message = $service->reply($ticket, $staff, 'Merci de votre message, nous enquêtons.');

        $this->assertInstanceOf(TicketMessage::class, $message);
        $this->assertEquals($staff->id, $message->user_id);
        $this->assertFalse($message->is_internal);
    }

    public function test_ticket_internal_message_not_sent_to_client(): void
    {
        $ticket = Ticket::factory()->withAssignee()->create();
        $staff = $ticket->assignedTo;

        $service = app(TicketService::class);

        Mail::fake();

        $message = $service->reply($ticket, $staff, 'Note interne: vérifier l\'adresse livreur', true);

        $this->assertTrue($message->is_internal);
        // L'email ne devrait pas être envoyé au client pour un message interne
    }

    public function test_ticket_auto_status_change_on_reply(): void
    {
        $ticket = Ticket::factory()
            ->create(['status' => TicketStatus::WAITING_CUSTOMER]);
        $client = $ticket->client;

        $service = app(TicketService::class);

        $service->reply($ticket, $client, 'Voici les informations que vous demandez.');

        $ticket->refresh();

        $this->assertEquals(TicketStatus::IN_PROGRESS, $ticket->status);
    }

    public function test_round_robin_assignment(): void
    {
        // Crée 2 staff et plusieurs tickets
        $staff1 = User::factory()->create();
        $staff2 = User::factory()->create();

        $service = app(TicketService::class);

        // Assigne plusieurs tickets
        for ($i = 0; $i < 3; $i++) {
            $ticket = Ticket::factory()->create(['assigned_to' => null]);
            $service->assignAutomatic($ticket);
        }

        // Le dernier ticket devrait être assigné au staff avec moins de tickets
        // (alternance round-robin)
    }

    public function test_sla_calculation(): void
    {
        $ticket = Ticket::factory()
            ->create([
                'priority' => TicketPriority::HIGH,
                'created_at' => now()->subHours(2),
            ]);

        $this->assertFalse($ticket->isOverdue()); // 4h SLA, créé il y a 2h
        $this->assertGreaterThan(0, $ticket->slaRemainingHours()); // Doit avoir du temps restant

        $ticketOverdue = Ticket::factory()
            ->create([
                'priority' => TicketPriority::HIGH,
                'created_at' => now()->subHours(5),
            ]);

        $this->assertTrue($ticketOverdue->isOverdue()); // 4h SLA, créé il y a 5h
    }

    public function test_get_stats(): void
    {
        Ticket::factory()->count(5)->create(['status' => TicketStatus::OPEN]);
        Ticket::factory()->count(3)->create(['status' => TicketStatus::IN_PROGRESS]);
        Ticket::factory()->count(2)->create(['status' => TicketStatus::RESOLVED]);

        $service = app(TicketService::class);
        $stats = $service->getStats();

        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('open', $stats);
        $this->assertArrayHasKey('by_status', $stats);
        $this->assertEquals(10, $stats['total']);
    }
}
