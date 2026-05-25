<?php

declare(strict_types=1);

namespace App\Domains\Returns\Services;

use App\Domains\Returns\Models\Rma;
use App\Domains\Returns\Models\RmaItem;
use App\Domains\Returns\Events\RmaApproved;
use App\Domains\Returns\Events\RmaRefunded;
use App\Enums\RmaStatus;
use App\Enums\ReturnReason;
use App\Enums\ItemCondition;
use App\Models\User;
use App\Domains\Order\Models\Order;
use App\Domains\Order\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class RmaService
{
    private const RETURN_WINDOW_DAYS = 14;
    private const RMA_NUMBER_LENGTH = 5;

    /**
     * Demande un retour pour une commande.
     * Vérifie les délais et crée le ticket RMA.
     */
    public function requestReturn(Order $order, User $user, array $itemsData, ?string $notes = null): Rma
    {
        // Vérifier que la commande est livrée
        if ($order->status !== 'delivered') {
            throw new \Exception('La commande doit être livrée pour demander un retour.');
        }

        // Vérifier le délai (14 jours après delivery)
        $deliveredAt = $order->delivered_at ?? $order->updated_at;
        if (Carbon::parse($deliveredAt)->addDays(self::RETURN_WINDOW_DAYS)->isPast()) {
            throw new \Exception('Délai de retour dépassé (14 jours après livraison).');
        }

        // Créer le RMA
        $rma = Rma::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'rma_number' => null,
            'status' => RmaStatus::REQUESTED,
            'reason' => $itemsData['reason'],
            'customer_notes' => $notes,
        ]);

        // Ajouter les articles
        foreach ($itemsData['items'] as $item) {
            $orderItem = $order->items()->findOrFail($item['order_item_id']);

            RmaItem::create([
                'rma_id' => $rma->id,
                'order_item_id' => $orderItem->id,
                'quantity' => $item['quantity'],
                'refund_amount' => $orderItem->getPriceInEuros() * $item['quantity'], // Prix original
            ]);
        }

        return $rma->refresh();
    }

    /**
     * Approuve une demande de retour.
     */
    public function approve(Rma $rma, User $admin): Rma
    {
        if (!$rma->canBeApproved()) {
            throw new \Exception("Le RMA ne peut pas être approuvé (statut: {$rma->status->label()}).");
        }

        $rma->update([
            'status' => RmaStatus::APPROVED,
            'rma_number' => $this->generateRmaNumber(),
            'approved_by' => $admin->id,
            'admin_notes' => $admin->name . ' a approuvé ce RMA le ' . now()->format('d/m/Y H:i'),
        ]);

        RmaApproved::dispatch($rma);

        return $rma->refresh();
    }

    /**
     * Enregistre la réception des articles retournés.
     */
    public function receiveItems(Rma $rma): Rma
    {
        if (!$rma->canBeReceived()) {
            throw new \Exception("Le RMA n'est pas dans le bon statut pour recevoir des articles.");
        }

        $rma->update(['status' => RmaStatus::RECEIVED]);

        return $rma->refresh();
    }

    /**
     * Enregistre l'inspection des articles.
     */
    public function inspectItems(Rma $rma, array $conditions): Rma
    {
        if (!$rma->canBeInspected()) {
            throw new \Exception("Le RMA n'est pas prêt pour inspection.");
        }

        // Mettre à jour les conditions des articles
        foreach ($conditions as $itemId => $condition) {
            RmaItem::where('rma_id', $rma->id)
                ->where('order_item_id', $itemId)
                ->update(['condition' => $condition]);
        }

        $rma->update(['status' => RmaStatus::INSPECTED]);

        return $rma->refresh();
    }

    /**
     * Traite le remboursement après inspection.
     */
    public function processRefund(Rma $rma, User $admin): Rma
    {
        if (!$rma->canBeRefunded()) {
            throw new \Exception("Le RMA n'est pas prêt pour remboursement.");
        }

        // Calculer le montant de remboursement
        $refundAmount = $this->calculateRefundAmount($rma);

        // Créer le refund dans le Payment
        // (Cette partie dépend de votre système de paiement)
        // Pour l'instant, on enregistre juste le montant

        $rma->update([
            'status' => RmaStatus::REFUNDED,
            'refund_amount' => $refundAmount,
            'refunded_at' => now(),
            'admin_notes' => ($rma->admin_notes ?? '') . "\n\nRemboursement de {$refundAmount}€ traité par {$admin->name} le " . now()->format('d/m/Y H:i'),
        ]);

        // Remettre en stock si applicable
        $this->restockItems($rma);

        RmaRefunded::dispatch($rma);

        return $rma->refresh();
    }

    /**
     * Rejette une demande de retour.
     */
    public function reject(Rma $rma, string $reason): Rma
    {
        if ($rma->isFinalized()) {
            throw new \Exception('Impossible de rejeter un RMA finalisé.');
        }

        $rma->update([
            'status' => RmaStatus::REJECTED,
            'admin_notes' => ($rma->admin_notes ?? '') . "\n\nRejeté: {$reason}",
        ]);

        return $rma->refresh();
    }

    /**
     * Calcule le montant de remboursement selon la raison.
     */
    private function calculateRefundAmount(Rma $rma): float
    {
        $total = 0;

        foreach ($rma->items as $item) {
            $amount = $item->refund_amount;

            // Si changement d'avis, pas de remboursement des frais de port
            if ($rma->reason === ReturnReason::CHANGED_MIND) {
                // Déduire une partie des frais (exemple: 10€ par item)
                // $amount -= min(10, $amount * 0.1);
            }

            $total += $amount;
        }

        // Ajouter les frais d'expédition si applicable
        if ($rma->reason->includesShipping() && $rma->order->shipping_cost) {
            $total += $rma->order->shipping_cost;
        }

        return (float) $total;
    }

    /**
     * Remet les articles en stock si applicable.
     */
    private function restockItems(Rma $rma): void
    {
        foreach ($rma->items as $item) {
            $orderItem = $item->orderItem;

            // Ne remettre en stock que si:
            // 1. L'article est UNOPENED
            // 2. OU la raison n'est pas CHANGED_MIND
            if (
                $item->condition === ItemCondition::UNOPENED ||
                ($rma->reason->restockItem() && $item->condition !== ItemCondition::DAMAGED)
            ) {
                // Augmenter le stock du produit
                $orderItem->product()->increment('stock', $item->quantity);
            }
        }
    }

    /**
     * Génère un numéro RMA unique.
     */
    private function generateRmaNumber(): string
    {
        $year = now()->year;
        $sequence = Rma::whereYear('created_at', $year)
            ->whereNotNull('rma_number')
            ->count() + 1;

        return sprintf('RMA-%d-%0' . self::RMA_NUMBER_LENGTH . 'd', $year, $sequence);
    }

    /**
     * Récupère les RMAs d'un utilisateur.
     */
    public function getUserRmas(User $user): Collection
    {
        return Rma::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Récupère les RMAs en attente d'approbation.
     */
    public function getPendingRmas(): Collection
    {
        return Rma::requested()
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Récupère les RMAs approuvés en attente de réception.
     */
    public function getAwaitingReceiptRmas(): Collection
    {
        return Rma::approved()
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Récupère les RMAs en attente d'inspection.
     */
    public function getAwaitingInspectionRmas(): Collection
    {
        return Rma::received()
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Récupère les statistiques des retours.
     */
    public function getStats(): array
    {
        return [
            'total' => Rma::count(),
            'pending' => Rma::requested()->count(),
            'approved' => Rma::approved()->count(),
            'received' => Rma::received()->count(),
            'inspected' => Rma::inspected()->count(),
            'refunded' => Rma::where('status', RmaStatus::REFUNDED)->count(),
            'rejected' => Rma::where('status', RmaStatus::REJECTED)->count(),
            'total_refunded' => Rma::where('status', RmaStatus::REFUNDED)->sum('refund_amount'),
        ];
    }
}
