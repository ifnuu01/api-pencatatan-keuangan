<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Carbon\Carbon;

class ProcessRecurringTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-recurring-transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and generate transactions from recurring transactions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $recurrings = RecurringTransaction::where('is_active', true)
            ->where('next_run_date', '<=', $now)
            ->get();

        foreach ($recurrings as $recurring) {
            $wallet = $recurring->wallet;

            if ($recurring->type === 'expense' && $wallet->balance < $recurring->amount) {
                continue;
            }

            Transaction::create([
                'user_id' => $recurring->user_id,
                'wallet_id' => $recurring->wallet_id,
                'category_id' => $recurring->category_id,
                'type' => $recurring->type,
                'amount' => $recurring->amount,
                'description' => $recurring->description,
                'transaction_date' => $now,
            ]);

            if ($recurring->type === 'expense') {
                $wallet->balance -= $recurring->amount;
            } else {
                $wallet->balance += $recurring->amount;
            }
            $wallet->save();

            $nextRun = Carbon::parse($recurring->next_run_date);
            switch ($recurring->repeat_interval) {
                case 'daily':
                    $nextRun->addDays($recurring->repeat_every);
                    break;
                case 'weekly':
                    $nextRun->addWeeks($recurring->repeat_every);
                    break;
                case 'monthly':
                    $nextRun->addMonths($recurring->repeat_every);
                    break;
                case 'yearly':
                    $nextRun->addYears($recurring->repeat_every);
                    break;
            }
            $recurring->next_run_date = $nextRun;

            if (
                ($recurring->end_date && $nextRun->gt(Carbon::parse($recurring->end_date))) ||
                ($recurring->total_occurences && $recurring->total_occurences <= 1)
            ) {
                $recurring->is_active = false;
            } else if ($recurring->total_occurences) {
                $recurring->total_occurences -= 1;
            }

            $recurring->save();
        }
        $this->info('Recurring transactions processed.');
    }
}
