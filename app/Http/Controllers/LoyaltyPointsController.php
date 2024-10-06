<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelRequest;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\WithdrawRequest;
use App\Services\LoyaltyPointsServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class LoyaltyPointsController extends Controller
{
    private LoyaltyPointsServiceInterface $loyaltyPointsService;

    public function __construct(LoyaltyPointsServiceInterface $loyaltyPointsService)
    {
        $this->loyaltyPointsService = $loyaltyPointsService;
    }

    public function deposit(DepositRequest $request): JsonResponse
    {
        $data = $request->validated();

        Log::info('Deposit transaction input: ' . print_r($data, true));

        $account = $this->loyaltyPointsService->findAccount($data['account_type'], $data['account_id']);
        if (!$account) {
            Log::info('Account is not found');
            return response()->json(['message' => 'Account is not found'], 400);
        }

        if (!$account->active) {
            Log::info('Account is not active');
            return response()->json(['message' => 'Account is not active'], 400);
        }

        $transaction = $this->loyaltyPointsService->execTransaction($account, $data);
        Log::info($transaction);

        $this->loyaltyPointsService->sendNotifications($account, $transaction);

        return response()->json($transaction);

    }

    public function cancel(CancelRequest $request): JsonResponse
    {
        $data = $request->validated();
        $reason = $data['cancellation_reason'];

        $transaction = $this->loyaltyPointsService->findActiveTransaction($data['transaction_id']);

        if (!$transaction) {
            return response()->json(['message' => 'Transaction is not found'], 400);
        }

        $this->loyaltyPointsService->cancelTransaction($transaction, $reason);

        return response()->json(['message' => 'Transaction canceled successfully']);

    }

    public function withdraw(WithdrawRequest $request): JsonResponse
    {
        $data = $request->validated();

        Log::info('Withdraw loyalty points transaction input: ' . print_r($data, true));

        $account = $this->loyaltyPointsService->findAccount($data['account_type'], $data['account_id']);

        if (!$account) {
            Log::info('Account not found for type: ' . $data['account_type'] . ', id: ' . $data['account_id']);
            return response()->json(['message' => 'Account is not found'], 400);
        }

        if (!$account->active) {
            Log::info('Account is not active for type: ' . $data['account_type'] . ', id: ' . $data['account_id']);
            return response()->json(['message' => 'Account is not active'], 400);
        }

        if ($data['points_amount'] <= 0) {
            Log::info('Wrong loyalty points amount: ' . $data['points_amount']);
            return response()->json(['message' => 'Wrong loyalty points amount'], 400);
        }

        if ($account->getBalance() < $data['points_amount']) {
            Log::info('Insufficient funds on account: ' . $data['points_amount']);
            return response()->json(['message' => 'Insufficient funds on account'], 400);
        }

        $transaction = $this->loyaltyPointsService->execWithdrawal($account, $data);
        Log::info($transaction);

        return response()->json($transaction);
    }
}
