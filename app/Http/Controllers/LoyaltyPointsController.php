<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelRequest;
use App\Http\Requests\DepositRequest;
use App\Mail\LoyaltyPointsReceived;
use App\Models\LoyaltyAccount;
use App\Models\LoyaltyPointsTransaction;
use App\Services\LoyaltyPointsServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

    public function withdraw()
    {
        $data = $_POST;

        Log::info('Withdraw loyalty points transaction input: ' . print_r($data, true));

        $type = $data['account_type'];
        $id = $data['account_id'];
        if (($type == 'phone' || $type == 'card' || $type == 'email') && $id != '') {
            if ($account = LoyaltyAccount::where($type, '=', $id)->first()) {
                if ($account->active) {
                    if ($data['points_amount'] <= 0) {
                        Log::info('Wrong loyalty points amount: ' . $data['points_amount']);
                        return response()->json(['message' => 'Wrong loyalty points amount'], 400);
                    }
                    if ($account->getBalance() < $data['points_amount']) {
                        Log::info('Insufficient funds: ' . $data['points_amount']);
                        return response()->json(['message' => 'Insufficient funds'], 400);
                    }

                    $transaction = LoyaltyPointsTransaction::withdrawLoyaltyPoints($account->id, $data['points_amount'], $data['description']);
                    Log::info($transaction);
                    return $transaction;
                } else {
                    Log::info('Account is not active: ' . $type . ' ' . $id);
                    return response()->json(['message' => 'Account is not active'], 400);
                }
            } else {
                Log::info('Account is not found:' . $type . ' ' . $id);
                return response()->json(['message' => 'Account is not found'], 400);
            }
        } else {
            Log::info('Wrong account parameters');
            throw new \InvalidArgumentException('Wrong account parameters');
        }
    }
}
