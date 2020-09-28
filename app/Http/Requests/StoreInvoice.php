<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Account;
use App\Product;

class StoreInvoice extends FormRequest
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
            'customer_id.required' => 'The customer field is required.',
            'customer_id.exists' =>
                'The selected customer is invalid. Please choose among the recommended items.',
            'invoice_number.min' => 'The invoice number must be a positive number.',
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
            'customer_id' => ['required', 'exists:App\Customer,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date'],
            'invoice_number' => ['required', 'min:1'],
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
            if (!is_int(filter_var(request('invoice_number'), FILTER_VALIDATE_INT)))
            {
                $validator->errors()->add('invoice_number', 'Invoice number must be an integer.');
            }
            if (is_null(request("item_lines.'product_id'"))) {
                $validator->errors()->add('lines', 'Please enter at least one line item.');
            }
            if (!is_null(request("item_lines.'product_id'"))) {
                $this->validateItemLines($validator);
            }
        });
    }
    public function validateItemLines($validator)
    {
        $count = count(request("item_lines.'product_id'"));
        for ($row = 0; $row < $count; $row++) {
            $productExists = Product::where('id', request("item_lines.'product_id'.".$row))->exists();
            if (!$productExists) {
                $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                    ': Product is invalid. Please choose among the recommended items.');
            }
            $this->validateItemQuantity($validator, $row, $productExists);
            $this->validateItemAmount($validator, $row);
            if (!is_null(request("item_lines.'input_tax'.".$row))) {
                if (is_numeric(request("item_lines.'input_tax'.".$row))) {
                    if (request("item_lines.'input_tax'.".$row) < 0.00) {
                        $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                            ': Tax must be positive.');
                    }
                    if (request("item_lines.'input_tax'.".$row) > request("item_lines.'amount'.".$row) * 0.12) {
                        $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                            ': Tax should not exceed 12% of line amount.');
                    }
                }
                else {
                    $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                        ': Tax must be a number.');
                }
            }
        }
    }
    public function validateItemQuantity($validator, $row, $productExists)
    {
        if (is_null(request("item_lines.'quantity'.".$row))) {
            if ($productExists) {
                $product = Product::where('id', request("item_lines.'product_id'.".$row))->firstOrFail();
                if ($product->track_quantity) {
                    $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                        ': Quantity required for tracked Inventory item.');
                }
            }
        }
        else {
            if (is_numeric(request("item_lines.'quantity'.".$row))) {
                if (request("item_lines.'quantity'.".$row) < 0.001) {
                    $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                        ': Quantity must be at least 0.001.');
                }
            }
            else {
                $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                    ': Quantity must be a number.');
            }
        }
    }
    public function validateItemAmount($validator, $row)
    {
        if (is_null(request("item_lines.'amount'.".$row))) {
            $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                ': Amount required.');
        }
        else {
            if (is_numeric(request("item_lines.'amount'.".$row))) {
                if (request("item_lines.'amount'.".$row) < 0.01) {
                    $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                        ': Amount must be positive.');
                }
            }
            else {
                $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                    ': Amount must be a number.');
            }
        }
    }
}
