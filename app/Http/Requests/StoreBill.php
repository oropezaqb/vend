<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Account;
use App\Product;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */

class StoreBill extends FormRequest
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
            'supplier_id.required' => 'The supplier field is required.',
            'supplier_id.exists' =>
                'The selected supplier is invalid. Please choose among the recommended items.',
            'bill_number.min' => 'The bill number must be a positive number.',
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
            'supplier_id' => ['required', 'exists:App\Supplier,id'],
            'bill_date' => ['required', 'date'],
            'due_date' => ['required', 'date'],
            'bill_number' => ['required', 'min:1'],
            "category_lines.'account_id'" => ['sometimes'],
            "category_lines.'account_id'.*" => [
                'sometimes'
            ],
            "category_lines.'amount'.*" => ['sometimes'],
            "category_lines.'input_tax'.*" => ['sometimes', 'numeric', 'min:0.00', 'nullable'],
            "item_lines.'product_id'" => ['sometimes'],
            "item_lines.'product_id'.*" => [
                'sometimes'
            ],
            "item_lines.'quantity'.*" => ['sometimes'],
            "item_lines.'amount'.*" => ['sometimes'],
            "item_lines.'input_tax'.*" => ['sometimes', 'numeric', 'min:0.00', 'nullable'],
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!is_int(filter_var(request('bill_number'), FILTER_VALIDATE_INT))) {
                $validator->errors()->add('bill_number', 'Bill number must be an integer.');
            }
            if (is_null(request("category_lines.'account_id'")) && is_null(request("item_lines.'product_id'"))) {
                $validator->errors()->add('lines', 'Please enter at least one line item.');
            }
            if (!is_null(request("category_lines.'account_id'"))) {
                $this->validateCategoryLines($validator);
            }
            if (!is_null(request("item_lines.'product_id'"))) {
                $this->validateItemLines($validator);
            }
        });
    }
    public function validateCategoryLines($validator)
    {
        $count = count(request("category_lines.'account_id'"));
        for ($row = 0; $row < $count; $row++) {
            if (!Account::where('id', request("category_lines.'account_id'.".$row))->exists()) {
                $validator->errors()->add('category_lines', 'Category line ' . ($row + 1) .
                    ': Account is invalid. Please choose among the recommended items.');
            }
            if (is_null(request("category_lines.'amount'.".$row))) {
                $validator->errors()->add('category_lines', 'Category line ' . ($row + 1) .
                    ': Amount required.');
            } else {
                if (is_numeric(request("category_lines.'amount'.".$row))) {
                    if (request("category_lines.'amount'.".$row) < 0.01) {
                        $validator->errors()->add('category_lines', 'Category line ' . ($row + 1) .
                            ': Amount must be positive.');
                    }
                } else {
                    $validator->errors()->add('category_lines', 'Category line ' . ($row + 1) .
                        ': Amount must be a number.');
                }
            }
        }
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
                    if (request("item_lines.'input_tax'.".$row) < 0.01) {
                        $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                            ': Amount must be positive.');
                    }
                    if (request("item_lines.'input_tax'.".$row) > request("item_lines.'amount'.".$row) * 0.12) {
                        $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                            ': Tax should not exceed 12% of line amount.');
                    }
                } else {
                    $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                        ': Amount must be a number.');
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
        } else {
            if (is_numeric(request("item_lines.'quantity'.".$row))) {
                if (request("item_lines.'quantity'.".$row) < 0.001) {
                    $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                        ': Quantity must be at least 0.001.');
                }
            } else {
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
        } else {
            if (is_numeric(request("item_lines.'amount'.".$row))) {
                if (request("item_lines.'amount'.".$row) < 0.01) {
                    $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                        ': Amount must be positive.');
                }
            } else {
                $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                    ': Amount must be a number.');
            }
        }
    }
}
