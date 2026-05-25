<?php

declare(strict_types=1);

namespace App\Http\Livewire\Account;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LoyaltyDashboard extends Component
{
    public int $points = 0;
    public float $totalSpent = 0;
    public string $tier = 'bronze';
    public array $recentTransactions = [];

    public function mount(): void
    {
        $user = Auth::user();
        
        // Récupérer les points de fidélité
        $loyaltyProgram = $user->loyaltyAccount;
        if ($loyaltyProgram) {
            $this->points = $loyaltyProgram->points;
            $this->tier = $loyaltyProgram->tier;
            $this->totalSpent = $loyaltyProgram->total_spent;
        }

        // Récupérer les 5 dernières transactions
        $this->recentTransactions = $user->loyaltyTransactions()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($t) => [
                'description' => $t->description,
                'points' => $t->points,
                'created_at' => $t->created_at->format('d/m/Y'),
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.account.loyalty-dashboard');
    }
}
