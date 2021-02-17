<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Product;
use App\Jobs\CreateInventoryQtyAdj;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */

class StoreInventoryQtyAdj extends FormRequest
{

    public $thereIsAmount = false;

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
            'number.min' => 'The Supplier Credit number must be positive.',
            "item_lines.'product_id'.*.exists" =>
                'Some products are invalid.',
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
            'number' => ['required', 'numeric', 'min:1'],
            "item_lines.'product_id'.*" => [
                'required',
                'exists:App\Product,id'
            ],
            "item_lines.'change_in_qty'.*" => ['sometimes', 'numeric', 'nullable'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            global $thereIsAmount;
            $company = \Auth::user()->currentCompany->company;
            if (!is_int(filter_var(request('number'), FILTER_VALIDATE_INT))) {
                $validator->errors()->add('number', 'Reference number must be an integer.');
            }
            if (!is_null(request("item_lines.'product_id'"))) {
                $this->validateItemLines($validator);
            }
            if (!$thereIsAmount) {
                $validator->errors()->add(
                    'item_lines',
                    'Lines: There should be at least one change in quantity.'
                );
            }
        });
    }
    public function validateItemLines($validator)
    {
        $count = count(request("item_lines.'product_id'"));
        global $thereIsAmount;
        for ($row = 0; $row < $count; $row++) {
            $productExists = Product::where('id', request("item_lines.'product_id'.".$row))->exists();
            if (!$productExists) {
                $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                    ': Product is invalid.');
            }
            if (is_numeric(request("item_lines.'change_in_qty'.".$row))) {
                $thereIsAmount = true;
            }
            $this->validateItemQuantity($validator, $row);
        }
    }
    public function validateItemQuantity($validator, $row)
    {
        if (is_null(request("item_lines.'change_in_qty'.".$row))) {
            $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                ': Change in quantity is required.');
        } else {
            if (!is_numeric(request("item_lines.'change_in_qty'.".$row))) {
                $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                    ': Change in quantity must be a number.');
            } else {
                if (request("item_lines.'change_in_qty'.".$row) == 0) {
                    $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                        ': Change in quantity must not be zero.');
                }
            }
        }
    }

}
