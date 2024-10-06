<?php

namespace App\Services;

use App\Models\LoyaltyPointsTransaction;

class LoyaltyPointsService implements LoyaltyPointsServiceInterface
{

    public function findActiveTransaction(int $transactionId): LoyaltyPointsTransaction
    {
        return LoyaltyPointsTransaction::where('id', $transactionId)
            ->where('canceled', 0)
            ->first();
    }

    public function cancelTransaction(LoyaltyPointsTransaction $transaction, string $reason): void
    {
        $transaction->canceled = now();
        $transaction->cancellation_reason = $reason;
        $transaction->save();
    }
}
