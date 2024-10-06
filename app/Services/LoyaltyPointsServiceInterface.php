<?php

namespace App\Services;

use App\Models\LoyaltyAccount;
use App\Models\LoyaltyPointsTransaction;

interface LoyaltyPointsServiceInterface
{
    public function findActiveTransaction(int $transactionId): LoyaltyPointsTransaction;

    public function cancelTransaction(LoyaltyPointsTransaction $transaction, string $reason): void;

    public function findAccount(string $type, string $id): LoyaltyAccount;

    public function execTransaction(LoyaltyAccount $account, array $data);

    public function sendNotifications(LoyaltyAccount $account, LoyaltyPointsTransaction $transaction): void;
}
