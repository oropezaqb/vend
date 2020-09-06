<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Account;

class StoreProduct extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function messages()
    {
        return [
            'receivable_account_id.required' => 'The receivable account is required.',
            'receivable_account_id.exists' =>
                'The selected receivable account is invalid. Please choose among the recommended items.',
            'income_account_id.required' => 'The income account is required.',
            'income_account_id.exists' =>
                'The selected income account is invalid. Please choose among the recommended items.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required'],
            'receivable_account_id' => ['required', 'exists:App\Account,id'],
            'inventory_account_id' => ['sometimes'],
            'income_account_id' => ['required', 'exists:App\Account,id'],
            'expense_account_id' => ['sometimes'],
        ];
    }

    public function withValidator($validator)
    {
        if (!request('track_quantity') == 1) {
            return;
        }
        $validator->after(function ($validator) {
            if (!Account::where('id', request('inventory_account_id'))->exists()) {
                $validator->errors()->add(
                    'inventory_account',
                    'Inventory account is invalid. Please choose among the recommended items.'
                );
            }
            if (!Account::where('id', request('expense_account_id'))->exists()) {
                $validator->errors()->add(
                    'expense_account',
                    'Expense account is invalid. Please choose among the recommended items.'
                );
            }
        });
    }
}
