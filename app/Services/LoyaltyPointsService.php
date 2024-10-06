<?php

namespace App\Services;

use App\Mail\LoyaltyPointsReceived;
use App\Models\LoyaltyAccount;
use App\Models\LoyaltyPointsTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

    public function findAccount(string $type, string $id): LoyaltyAccount
    {
        return LoyaltyAccount::where($type, $id)->first();
    }

    public function execTransaction(LoyaltyAccount $account, array $data): LoyaltyPointsTransaction
    {
        return LoyaltyPointsTransaction::performPaymentLoyaltyPoints(
            $account->id,
            $data['loyalty_points_rule'],
            $data['description'],
            $data['payment_id'],
            $data['payment_amount'],
            $data['payment_time']);
    }

    public function sendNotifications(LoyaltyAccount $account, LoyaltyPointsTransaction $transaction): void
    {
        if ($account->email && $account->email_notification) {
            Mail::to($account)->send(new LoyaltyPointsReceived($transaction->points_amount, $account->getBalance()));
        }

        if ($account->phone && $account->phone_notification) {
            // instead SMS component
            Log::info('You received ' . $transaction->points_amount . '. Your balance: ' . $account->getBalance());
        }
    }

    public function execWithdrawal(LoyaltyAccount $account, array $data): LoyaltyPointsTransaction
    {
        return LoyaltyPointsTransaction::withdrawLoyaltyPoints($account->id, $data['points_amount'], $data['description']);
    }
}
