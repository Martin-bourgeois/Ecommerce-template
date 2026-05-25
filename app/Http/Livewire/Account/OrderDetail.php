<?php

declare(strict_types=1);

namespace App\Http\Livewire\Account;

use App\Domains\Order\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class OrderDetail extends Component
{
    use WithFileUploads;

    public Order $order;
    public array $orderData = [];
    public array $items = [];
    public $paypalProof;
    public $paypalTransactionId = '';
    public string $message = '';
    public string $messageType = ''; // 'success' or 'error'

    public function mount(Order $order): void
    {
        // Ensure user owns this order
        if ($order->user_id !== Auth::id()) {
            $this->redirectRoute('account.orders');
            return;
        }

        $this->order = $order;
        $this->loadOrderData();
        $this->paypalTransactionId = $this->order->paypal_transaction_id ?? '';
    }

    private function loadOrderData(): void
    {
        $this->orderData = [
            'id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status' => $this->order->status,
            'total_cents' => $this->order->total_cents,
            'created_at' => $this->order->created_at->format('d/m/Y H:i'),
            'paypal_proof_path' => $this->order->paypal_proof_path,
            'payment_verified_at' => $this->order->payment_verified_at,
        ];

        $this->items = $this->order->items()
            ->with('product')
            ->get()
            ->map(fn($item) => [
                'product_name' => $item->name,
                'qty' => $item->qty,
                'price_cents' => $item->price_cents,
                'total_cents' => $item->total_cents,
            ])
            ->toArray();
    }

    public function uploadPaypalProof(): void
    {
        $this->validate([
            'paypalProof' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            'paypalTransactionId' => 'required|string|min:10',
        ]);

        try {
            // Store the file
            $path = $this->paypalProof->store('paypal-proofs', 'private');
            
            // Update order
            $this->order->update([
                'paypal_proof_path' => $path,
                'paypal_transaction_id' => $this->paypalTransactionId,
            ]);

            // Reload data
            $this->order->refresh();
            $this->loadOrderData();
            
            $this->message = '✓ Preuve de paiement téléchargée avec succès!';
            $this->messageType = 'success';
            
            // Clear form
            $this->paypalProof = null;
            $this->paypalTransactionId = '';
            
            // Clear message after 5 seconds
            $this->dispatch('clearMessage');
        } catch (\Exception $e) {
            $this->message = '✗ Erreur lors du téléchargement: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function downloadPaypalProof(): void
    {
        if (!$this->order->paypal_proof_path) {
            $this->message = 'Aucune preuve de paiement disponible';
            $this->messageType = 'error';
            return;
        }

        // Redirect to download endpoint
        $this->redirect(route('order.download-proof', ['order' => $this->order->id]));
    }

    public function render()
    {
        return view('livewire.account.order-detail');
    }
}

