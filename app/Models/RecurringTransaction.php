<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecurringTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'wallet_id',
        'category_id',
        'amount',
        'type',
        'description',
        'start_date',
        'next_run_date',
        'repeat_interval',
        'repeat_every',
        'end_date',
        'total_occurences',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
