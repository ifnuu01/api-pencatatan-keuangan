<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $wallets = Wallet::where('user_id', $user->id)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'balance', 'currency']);

        return response()->json([
            'status' => true,
            'message' => 'Wallets retrieved successfully',
            'data' => $wallets,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'balance' => 'required|numeric|min:1',
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'balance' => $request->balance,
            'currency' => $request->get('currency', 'Rp'),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Wallet created successfully',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $wallet = Wallet::find($id);

        if (!$wallet) {
            return response()->json([
                'status' => false,
                'message' => 'Wallet not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Wallet retrieved successfully',
            'data' => $wallet,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $wallet = Wallet::find($id);

        if (!$wallet) {
            return response()->json([
                'status' => false,
                'message' => 'Wallet not found',
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'balance' => 'sometimes|required|numeric|min:1',
        ]);

        $wallet->update($request->only(['name', 'balance']));

        return response()->json([
            'status' => true,
            'message' => 'Wallet updated successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $wallet = Wallet::find($id);

        if (!$wallet) {
            return response()->json([
                'status' => false,
                'message' => 'Wallet not found',
            ], 404);
        }

        $wallet->delete();

        return response()->json([
            'status' => true,
            'message' => 'Wallet deleted successfully',
        ]);
    }
}
