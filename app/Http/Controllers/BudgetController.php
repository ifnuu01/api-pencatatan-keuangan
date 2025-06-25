<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Budget;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use Carbon\Carbon;

class BudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $budgets = Budget::with('category')
            ->where('user_id', Auth::id())
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderBy('start_date', 'desc')
            ->get(['id', 'name', 'amount', 'period', 'start_date', 'end_date', 'category_id']);

        $budgets = $budgets->map(function ($budget) {
            $now = Carbon::now();

            switch ($budget->period) {
                case 'daily':
                    $periodStart = $now->copy()->startOfDay();
                    $periodEnd = $now->copy()->endOfDay();
                    break;
                case 'weekly':
                    $periodStart = $now->copy()->startOfWeek();
                    $periodEnd = $now->copy()->endOfWeek();
                    break;
                case 'monthly':
                    $periodStart = $now->copy()->startOfMonth();
                    $periodEnd = $now->copy()->endOfMonth();
                    break;
                case 'yearly':
                    $periodStart = $now->copy()->startOfYear();
                    $periodEnd = $now->copy()->endOfYear();
                    break;
                default:
                    $periodStart = Carbon::parse($budget->start_date);
                    $periodEnd = Carbon::parse($budget->end_date);
            }

            $budgetStart = Carbon::parse($budget->start_date);
            $budgetEnd = Carbon::parse($budget->end_date);

            $periodStart = $periodStart->lessThan($budgetStart) ? $budgetStart : $periodStart;
            $periodEnd = $periodEnd->greaterThan($budgetEnd) ? $budgetEnd : $periodEnd;

            $total_expense = Transaction::where('user_id', Auth::id())
                ->where('category_id', $budget->category_id)
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [$periodStart, $periodEnd])
                ->sum('amount');

            $remaining = $budget->amount - $total_expense;
            return [
                'id' => $budget->id,
                'name' => $budget->name,
                'amount' => $budget->amount,
                'period' => $budget->period,
                'start_date' => $budget->start_date,
                'end_date' => $budget->end_date,
                'category' => $budget->category,
                'total_expense' => $total_expense,
                'remaining' => $remaining,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Budgets retrieved successfully',
            'data' => $budgets,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'period' => 'required|in:daily,weekly,monthly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'category_id' => 'required|exists:categories,id',
        ]);

        Budget::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'amount' => $request->amount,
            'period' => $request->period,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'category_id' => $request->category_id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Budget created successfully',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $budget = Budget::with('category')
            ->where('user_id', Auth::id())
            ->find($id, ['id', 'name', 'amount', 'period', 'start_date', 'end_date', 'category_id']);

        if (!$budget) {
            return response()->json([
                'status' => false,
                'message' => 'Budget not found',
            ], 404);
        }

        $total_expense = Transaction::where('user_id', Auth::id())
            ->where('category_id', $budget->category_id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$budget->start_date, $budget->end_date])
            ->sum('amount');

        $remaining = $budget->amount - $total_expense;

        return response()->json([
            'status' => true,
            'message' => 'Budget retrieved successfully',
            'data' => [
                'id' => $budget->id,
                'name' => $budget->name,
                'amount' => $budget->amount,
                'period' => $budget->period,
                'start_date' => $budget->start_date,
                'end_date' => $budget->end_date,
                'category' => $budget->category,
                'total_expense' => $total_expense,
                'remaining' => $remaining,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $budget = Budget::where('user_id', Auth::id())->find($id);

        if (!$budget) {
            return response()->json([
                'status' => false,
                'message' => 'Budget not found',
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'amount' => 'sometimes|required|numeric|min:0',
            'period' => 'sometimes|required|in:daily,weekly,monthly,yearly',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'category_id' => 'sometimes|required|exists:categories,id',
        ]);

        $budget->update($request->only(['name', 'amount', 'period', 'start_date', 'end_date', 'category_id']));

        return response()->json([
            'status' => true,
            'message' => 'Budget updated successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $budget = Budget::where('user_id', Auth::id())->find($id);

        if (!$budget) {
            return response()->json([
                'status' => false,
                'message' => 'Budget not found',
            ], 404);
        }

        $budget->delete();

        return response()->json([
            'status' => true,
            'message' => 'Budget deleted successfully',
        ]);
    }
}
