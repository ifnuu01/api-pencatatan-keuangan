<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecurringTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
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
            'is_active' => 'nullable|boolean',
        ];
    }
}
