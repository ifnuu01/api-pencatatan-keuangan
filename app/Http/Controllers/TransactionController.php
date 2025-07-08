<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Http\Requests\TransactionRequest;
use App\Models\Wallet;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transactions = Transaction::with('category', 'wallet')
            ->where('user_id', $this->getAuthUser()->id)
            ->orderBy('transaction_date', 'desc')
            ->get(['id', 'wallet_id', 'category_id', 'type', 'amount', 'description', 'transaction_date']);

        $transactions->transform(function ($transaction) {
            $transaction->makeHidden(['wallet_id', 'category_id']);
            return $transaction;
        });

        return response()->json([
            'status' => true,
            'message' => 'transaction retrieved successfully',
            'data' => $transactions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TransactionRequest $request)
    {

        $wallet = Wallet::findOrFail($request->wallet_id);

        if ($request->type === 'expense') {
            $wallet->balance -= $request->amount;
        } else {
            $wallet->balance += $request->amount;
        }

        $wallet->save();

        Transaction::create([
            'user_id' => $this->getAuthUser()->id,
            'wallet_id' => $wallet->id,
            'category_id' => $request->category_id,
            'type' => $request->type,
            'amount' => $request->amount,
            'description' => $request->description,
            'transaction_date' => $request->transaction_date ?? now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Transaction created succesfully',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $transaction = Transaction::with('category', 'wallet')
            ->where('user_id', $this->getAuthUser()->id)
            ->find($id, ['id', 'wallet_id', 'category_id', 'type', 'amount', 'description', 'transaction_date']);

        if (!$transaction) {
            return response()->json([
                'status' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        $transaction->makeHidden(['wallet_id', 'category_id']);

        return response()->json([
            'status' => true,
            'message' => 'Transaction retrieved succesfully',
            'data' => $transaction,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TransactionRequest $request, string $id)
    {
        $transaction = Transaction::where('user_id', $this->getAuthUser()->id)->find($id);

        if (!$transaction) {
            return response()->json([
                'status' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        $oldWallet = Wallet::findOrFail($transaction->wallet_id);
        $newWallet = Wallet::findOrFail($transaction->wallet_id);

        if ($transaction->type === 'expense') {
            $oldWallet->balance += $transaction->amount;
        } else {
            $oldWallet->balance -= $transaction->amount;
        }

        if ($request->type === 'expense') {
            $newWallet->balance -= $request->amount;
        } else {
            $newWallet->balance += $request->amount;
        }

        $oldWallet->save();
        $newWallet->save();

        $transaction->update($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Transaction update successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $transaction = Transaction::where('user_id', $this->getAuthUser()->id)->find($id);

        if (!$transaction) {
            return response()->json([
                'status' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        $wallet = Wallet::findOrFail($transaction->wallet_id);

        if ($transaction->type === 'expense') {
            $wallet->balance += $transaction->amount;
        } else {
            $wallet->balance -= $transaction->amount;
        }

        $transaction->delete();

        return response()->json([
            'status' => true,
            'message' => 'Transaction deleted successfully',
        ]);
    }
}
