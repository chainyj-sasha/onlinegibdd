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

    /**
     * Processes a deposit transaction for an account.
     *
     * @param DepositRequest $request The request with validated transaction data.
     *
     * @return JsonResponse JSON response with transaction details or error message.
     */
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

    /**
     * Cancels an active transaction.
     *
     * @param CancelRequest $request The request with validated cancellation data.
     *
     * @return JsonResponse JSON response with a success message or an error if the transaction is not found.
     */
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

    /**
     * Withdrawal of loyalty points from an account.
     *
     * @param WithdrawRequest $request The request with validated withdrawal data.
     *
     * @return JsonResponse JSON response with transaction details or an error message.
     */
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
