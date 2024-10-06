<?php

namespace App\Services;

use App\Models\LoyaltyPointsTransaction;

interface LoyaltyPointsServiceInterface
{
    public function findActiveTransaction(int $transactionId): LoyaltyPointsTransaction;

    public function cancelTransaction(LoyaltyPointsTransaction $transaction, string $reason): void;
}
