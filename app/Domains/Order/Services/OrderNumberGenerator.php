<?php

declare(strict_types=1);

namespace App\Domains\Order\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderNumberGenerator
{
    private const LOCK_KEY = 'order_number_lock';
    private const SEQUENCE_KEY = 'order_number_sequence';
    private const LOCK_TIMEOUT = 5; // seconds
    private const CACHE_TTL = 86400; // 24 hours (same day)

    /**
     * Generate a unique order number.
     *
     * Format: ORD-{year}-{sequence}
     * Example: ORD-2026-000001
     */
    public function generate(): string
    {
        // Acquire lock
        $lock = Cache::lock(self::LOCK_KEY, self::LOCK_TIMEOUT);

        try {
            if (!$lock->get()) {
                throw new \RuntimeException('Could not acquire order number lock');
            }

            $year = now()->year;
            $cacheKey = self::SEQUENCE_KEY . ':' . $year;

            // Get current sequence for this year
            $sequence = (int) Cache::get($cacheKey, 0);
            $sequence++;

            // Store next sequence
            Cache::put($cacheKey, $sequence, self::CACHE_TTL);

            // Verify uniqueness in database
            $orderNumber = sprintf('ORD-%d-%06d', $year, $sequence);

            while (DB::table('orders')->where('order_number', $orderNumber)->exists()) {
                $sequence++;
                Cache::put($cacheKey, $sequence, self::CACHE_TTL);
                $orderNumber = sprintf('ORD-%d-%06d', $year, $sequence);
            }

            return $orderNumber;
        } finally {
            $lock->release();
        }
    }

    /**
     * Reset sequence for testing.
     */
    public function resetSequence(int $year = null): void
    {
        $year = $year ?? now()->year;
        $cacheKey = self::SEQUENCE_KEY . ':' . $year;
        Cache::forget($cacheKey);
    }
}
