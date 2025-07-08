<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RecurringTransactionRequest;
use App\Models\RecurringTransaction;

class RecurringTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $recurring = RecurringTransaction::where('user_id', $this->getAuthUser()->id)
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
    public function store(RecurringTransactionRequest $request)
    {

        RecurringTransaction::create([
            'user_id' => $this->getAuthUser()->id,
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
        $recurring = RecurringTransaction::where('user_id', $this->getAuthUser()->id)->findOrFail($id);

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
    public function update(RecurringTransactionRequest $request, string $id)
    {
        $recurring = RecurringTransaction::where('user_id', $this->getAuthUser()->id)->findOrFail($id);

        if (!$recurring) {
            return response()->json([
                'status' => false,
                'message' => 'Recurring transacition not found',
            ], 404);
        }

        $recurring->update($request->validated());

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
        $recurring = RecurringTransaction::where('user_id', $this->getAuthUser()->id)->findOrFail($id);

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
