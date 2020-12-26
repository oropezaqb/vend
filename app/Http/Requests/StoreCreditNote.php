<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Product;
use App\Http\Requests\StoreCreditNote;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */

class StoreCreditNote extends FormRequest
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
            'invoice_id.required' => 'The customer field is required.',
            'invoice_id.exists' =>
                'The invoice number is invalid.',
            'number.min' => 'The credit note number must be positive.',
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
            'invoice_id' => ['required', 'exists:App\Invoice,invoice_number'],
            'date' => ['required', 'date'],
            'number' => ['required', 'min:1'],
            "item_lines.'product_id'" => ['sometimes'],
            "item_lines.'product_id'.*" => [
                'sometimes'
            ],
            "item_lines.'quantity'.*" => ['sometimes'],
            "item_lines.'amount'.*" => ['sometimes'],
            "item_lines.'input_tax'.*" => ['sometimes'],
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!is_int(filter_var(request('number'), FILTER_VALIDATE_INT)))
            {
                $validator->errors()->add('number', 'Credit note number must be an integer.');
            }
            if (is_null(request("item_lines.'product_id'"))) {
                $validator->errors()->add('lines', 'Please enter at least one line item.');
            }
            if (!is_null(request("item_lines.'product_id'"))) {
                $creditNote = new StoreBill();
                $creditNote->validateItemLines($validator);
            }
        });
    }
}
