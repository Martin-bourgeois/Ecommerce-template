<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Order\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function downloadProof(Order $order): StreamedResponse
    {
        // Ensure user owns this order
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        // Check if proof exists
        if (!$order->paypal_proof_path || !Storage::disk('private')->exists($order->paypal_proof_path)) {
            abort(404, 'Proof not found');
        }

        // Get the file
        $file = Storage::disk('private')->path($order->paypal_proof_path);

        // Return file download
        return response()->download($file, "payment-proof-{$order->order_number}.pdf");
    }
}
