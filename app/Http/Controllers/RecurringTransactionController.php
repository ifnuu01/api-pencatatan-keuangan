<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RecurringTransaction;
use Illuminate\Support\Facades\Auth;

class RecurringTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $recurring = RecurringTransaction::where('user_id', Auth::id())
            ->orderBy('start_date', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Recurring transaction succesfully',
            'data' => $recurring,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'wallet_id' => 'required|exists:wallets,id',
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense',
            'start_date' => 'required|date',
            'repeat_interval' => 'required|in:daily,weekly,monthly,yearly',
            'repeat_every' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'total_occurences' => 'nullable|integer|min:1',
        ]);

        RecurringTransaction::create([
            'user_id' => Auth::id(),
            'wallet_id' => $request->wallet_id,
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'type' => $request->type,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'next_run_date' => $request->start_date,
            'repeat_interval' => $request->repeat_interval,
            'repeat_every' => $request->repeat_every,
            'end_date' => $request->end_date,
            'total_occurences' => $request->total_occurences,
            'is_active' => true,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Recurring transacition Created Succesfully'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $recurring = RecurringTransaction::where('user_id', Auth::id())->findOrFail($id);

        if (!$recurring) {
            return response()->json([
                'status' => false,
                'message' => 'Recurring transacition not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Recurring transaction retrieved succesfully',
            'data' => $recurring
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $recurring = RecurringTransaction::where('user_id', Auth::id())->findOrFail($id);

        if (!$recurring) {
            return response()->json([
                'status' => false,
                'message' => 'Recurring transacition not found',
            ], 404);
        }

        $recurring->update($request->only([
            'wallet_id',
            'category_id',
            'amount',
            'type',
            'description',
            'start_date',
            'repeat_interval',
            'repeat_every',
            'end_date',
            'total_occurences',
            'is_active'
        ]));

        return response()->json([
            'status' => true,
            'message' => 'Recurring transacition update Succesfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $recurring = RecurringTransaction::where('user_id', Auth::id())->findOrFail($id);

        if (!$recurring) {
            return response()->json([
                'status' => false,
                'message' => 'Recurring transacition not found',
            ], 404);
        }

        $recurring->delete();

        return response()->json([
            'status' => true,
            'message' => 'Recurring transacition deleted Succesfully'
        ]);
    }
}
