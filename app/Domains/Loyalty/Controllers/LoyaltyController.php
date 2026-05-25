<?php

declare(strict_types=1);

namespace App\Domains\Loyalty\Controllers;

use App\Domains\Loyalty\DTOs\LoyaltyAccountDTO;
use App\Domains\Loyalty\DTOs\LoyaltyTransactionDTO;
use App\Domains\Loyalty\Repositories\LoyaltyAccountRepository;
use App\Domains\Loyalty\Repositories\LoyaltyTransactionRepository;
use App\Domains\Loyalty\Responses\LoyaltyAccountResponse;
use App\Domains\Loyalty\Responses\LoyaltyTransactionResponse;
use App\Domains\Loyalty\Services\LoyaltyService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function __construct(
        private LoyaltyService $loyaltyService,
        private LoyaltyAccountRepository $accountRepository,
        private LoyaltyTransactionRepository $transactionRepository,
    ) {}

    /**
     * Get current user's loyalty account
     */
    public function getAccount(Request $request): LoyaltyAccountResponse
    {
        $account = $this->accountRepository->getOrCreateForUser($request->user());
        $dto = LoyaltyAccountDTO::fromModel($account);

        return new LoyaltyAccountResponse($dto);
    }

    /**
     * Get loyalty account history/transactions
     */
    public function getTransactions(Request $request)
    {
        $account = $this->accountRepository->getOrCreateForUser($request->user());
        $limit = (int) $request->query('limit', 25);

        $transactions = $this->transactionRepository->getRecentForAccount($account, $limit);
        $dtos = $transactions->map(fn($t) => LoyaltyTransactionDTO::fromModel($t));

        return LoyaltyTransactionResponse::collection($dtos);
    }

    /**
     * Spend points for discount
     */
    public function spendPoints(Request $request)
    {
        $validated = $request->validate([
            'points' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            $transaction = $this->loyaltyService->spendPoints(
                $request->user(),
                $validated['points'],
                $validated['reason'] ?? 'Discount redemption'
            );

            $dto = LoyaltyTransactionDTO::fromModel($transaction);

            return response()->json([
                'success' => true,
                'transaction' => new LoyaltyTransactionResponse($dto),
                'message' => 'Points redeemed successfully',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get loyalty summary for dashboard
     */
    public function getSummary(Request $request)
    {
        $summary = $this->loyaltyService->getSummary($request->user());

        return response()->json([
            'success' => true,
            'summary' => $summary,
        ]);
    }

    /**
     * Get top earners (admin only)
     */
    public function getTopEarners(Request $request)
    {
        $limit = (int) $request->query('limit', 10);
        $accounts = $this->accountRepository->getTopEarners($limit);

        $dtos = $accounts->map(fn($a) => LoyaltyAccountDTO::fromModel($a));

        return LoyaltyAccountResponse::collection($dtos);
    }
}
