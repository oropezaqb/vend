<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Invoice;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */

class StoreReceivedPayment extends FormRequest
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
            'customer_id.required' => 'The customer name field is required.',
            'customer_id.exists' =>
                'The selected customer name is invalid. Please choose among the recommended items.',
            'number.min' => 'The receipt number must be a positive number.',
            'account_id.required' => 'The account title ("Deposit to") field is required.',
            'account_id.exists' =>
                'The selected account title ("Deposit to") is invalid. Please choose among the recommended items.',
            "item_lines.'invoice_id'.*.exists" =>
                'Some invoice items are invalid.',
            "item_lines.'payment'.min" => 'There must be at least one payment.',
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
            'date' => ['required', 'date'],
            'customer_id' => ['required', 'exists:App\Customer,id'],
            'number' => ['required', 'numeric', 'min:1'],
            'account_id' => ['required', 'exists:App\Account,id'],
            "item_lines.'payment'" => ['sometimes', 'min:1'],
            "item_lines.'invoice_id'.*" => [
                'sometimes',
                'required',
                'exists:App\Invoice,id'
            ],
            "item_lines.'payment'.*" => ['sometimes'],
        ];
    }
    public function withValidator($validator)
    {
        if (is_null(request("item_lines.'invoice_id'"))) {
            return;
        }
        $validator->after(function ($validator) {
            $count = count(request("item_lines.'invoice_id'"));
            $thereIsPayment = false;
            for ($row = 0; $row < $count; $row++) {
                if (!is_null(request("item_lines.'payment'.".$row))) {
                    $invoice = Invoice::find(request("item_lines.'invoice_id'.".$row));
                    if (request('customer_id') != $invoice->customer_id) {
                        $validator->errors()->add('item_lines', 'Line ' . ($row + 1) .
                            ': Customer in invoice and customer in receipt must be the same.');
                    }
                    if (!is_numeric(request("item_lines.'payment'.".$row))) {
                        $validator->errors()->add('item_lines', 'Line ' . ($row + 1) .
                            ': Payment should be a number.');
                    } else {
                        if (request("item_lines.'payment'.".$row) <= 0) {
                            $validator->errors()->add('item_lines', 'Line ' . ($row + 1) .
                                ': Payment should be positive.');
                        } else {
                            $amountReceivable = $invoice->itemLines->sum('amount') +
                                $invoice->itemLines->sum('output_tax');
                            $amountPaid = \DB::table('received_payment_lines')
                                ->where('invoice_id', $invoice->id)->sum('amount');
                            $balance = $amountReceivable - $amountPaid;
                            if (request("item_lines.'payment'.".$row) > $balance) {
                                $validator->errors()->add('item_lines', 'Line ' . ($row + 1) .
                                    ': Payment should not exceed open balance.');
                            }
                        }
                    }
                    $thereIsPayment = true;
                }
            }
            if (!$thereIsPayment) {
                $validator->errors()->add(
                    'item_lines',
                    'There should be at least one payment.'
                );
            }
        });
    }
}
