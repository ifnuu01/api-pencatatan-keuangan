<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public $colors = [
        "bg-red-600"    => "#C62828",
        "bg-yellow-400" => "#FBC02D",
        "bg-green-400"  => "#43A047",
        "bg-blue-400"   => "#1E88E5",
        "bg-purple-400" => "#8E24AA",
        "bg-pink-400"   => "#D81B60",
        "bg-yellow-300" => "#FDD835",
        "bg-gray-400"   => "#BDBDBD",
        "bg-black"      => "#000000",
        "bg-white"      => "#FFFFFF",
    ];
    private function tailwindToHex($color, $colors)
    {
        if (isset($colors[$color])) {
            return $colors[$color];
        }
        if (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)) {
            return $color;
        }
        return '#000000';
    }

    public function index()
    {

        $colors = $this->colors;
        $user = $this->getAuthUser();
        $transactions = Transaction::with(['category', 'wallet'])
            ->where('user_id', $user->id)
            ->orderBy('transaction_date', 'desc')
            ->get();

        $pengeluaranKategori = $transactions->where('type', 'expense')
            ->groupBy('category_id')
            ->map(function ($items) use ($colors) {
                $category = $items->first()->category;
                return [
                    'name'  => $category->name ?? 'Lainnya',
                    'value' => $items->sum('amount'),
                    'color' => $this->tailwindToHex($category->color ?? null, $colors),
                ];
            })->values();

        $pemasukanKategori = $transactions->where('type', 'income')
            ->groupBy('category_id')
            ->map(function ($items) use ($colors) {
                $category = $items->first()->category;
                return [
                    'name'  => $category->name ?? 'Lainnya',
                    'value' => $items->sum('amount'),
                    'color' => $this->tailwindToHex($category->color ?? null, $colors),
                ];
            })->values();

        $transaksiTerbaru = $transactions->take(6)->map(function ($trx) use ($colors) {
            $category = $trx->category;
            return [
                'id'               => $trx->id,
                'type'             => $trx->type,
                'category'         => [
                    'icon'  => $category->icon ?? null,
                    'color' => $category->color ?? $colors['bg-black'],
                ],
                'transaction_date' => $trx->transaction_date,
                'amount'           => $trx->amount,
                'wallet'           => [
                    'currency' => $trx->wallet->currency ?? 'IDR',
                ],
            ];
        });
        $wallets = Wallet::where('user_id', $user->id)
            ->get(['id', 'name', 'balance', 'currency']);
        $saldo = $wallets->sum('balance');

        $totalPemasukan = $transactions->where('type', 'income')->sum('amount');
        $totalPengeluaran = $transactions->where('type', 'expense')->sum('amount');

        return response()->json([
            'pengeluaranKategori' => $pengeluaranKategori,
            'pemasukanKategori'   => $pemasukanKategori,
            'transaksiTerbaru'    => $transaksiTerbaru,
            'saldo'               => $saldo,
            'totalPemasukan'      => $totalPemasukan,
            'totalPengeluaran'    => $totalPengeluaran,
        ]);
    }

    public function charts()
    {
        $colors = $this->colors;
        $user = $this->getAuthUser();
        $transactions = Transaction::with(['category', 'wallet'])
            ->where('user_id', $user->id)
            ->orderBy('transaction_date', 'desc')
            ->get();

        $pengeluaranKategori = $transactions->where('type', 'expense')
            ->groupBy('category_id')
            ->map(function ($items) use ($colors) {
                $category = $items->first()->category;
                return [
                    'name'  => $category->name ?? 'Lainnya',
                    'value' => $items->sum('amount'),
                    'color' => $this->tailwindToHex($category->color ?? null, $colors),
                ];
            })->values();

        $pemasukanKategori = $transactions->where('type', 'income')
            ->groupBy('category_id')
            ->map(function ($items) use ($colors) {
                $category = $items->first()->category;
                return [
                    'name'  => $category->name ?? 'Lainnya',
                    'value' => $items->sum('amount'),
                    'color' => $this->tailwindToHex($category->color ?? null, $colors),
                ];
            })->values();

        $data = [];
        foreach ($transactions as $transaction) {
            $date = $transaction->transaction_date instanceof \DateTimeInterface
                ? $transaction->transaction_date
                : Carbon::parse($transaction->transaction_date);

            $monthName = $date->format('F');

            if (!array_key_exists($monthName, $data)) {
                $data[$monthName] = [
                    'month'        => $monthName,
                    'pemasukan'    => 0,
                    'pengeluaran'  => 0,
                ];
            }

            $amount = $transaction->amount;
            if ($transaction->type === 'income') {
                $data[$monthName]['pemasukan'] += $amount;
            } elseif ($transaction->type === 'expense') {
                $data[$monthName]['pengeluaran'] += $amount;
            }
        }

        return response()->json([
            'pengeluaranKategori' => $pengeluaranKategori,
            'pemasukanKategori'   => $pemasukanKategori,
            'data'                => array_values($data),
        ]);
    }
}
