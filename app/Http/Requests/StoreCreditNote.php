<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Product;
use App\Http\Requests\StoreCreditNote;
use App\Jobs\CreateCreditNote;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */

class StoreCreditNote extends FormRequest
{
    public $productQuantity = array();
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
            'invoice_id.required' => 'The invoice number is required.',
            'invoice_id.exists' =>
                'The invoice number is invalid. Please choose an existing invoice.',
            'number.min' => 'The credit note number must be positive.',
            "item_lines.'product_id'.*.exists" =>
                'Some products are invalid. Refresh this page.',
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
            'invoice_id' => ['required', 'exists:App\Invoice,id'],
            'date' => ['required', 'date'],
            'number' => ['required', 'numeric', 'min:1'],
            "item_lines.'product_id'.*" => [
                'required',
                'exists:App\Product,id'
            ],
            "item_lines.'quantity'.*" => ['sometimes', 'numeric', 'nullable'],
            "item_lines.'amount'.*" => ['sometimes', 'numeric', 'nullable'],
            "item_lines.'output_tax'.*" => ['sometimes', 'numeric', 'nullable'],
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!is_int(filter_var(request('number'), FILTER_VALIDATE_INT))) {
                $validator->errors()->add('number', 'Credit note number must be an integer.');
            }
            if (is_null(request("item_lines.'product_id'"))) {
                $validator->errors()->add('lines', 'Please select a valid invoice with corresponding products.');
            }
            if (!is_null(request("item_lines.'product_id'"))) {
                $this->validateItemLines($validator);
            }
        });
    }
    public function validateItemLines($validator)
    {
        $count = count(request("item_lines.'product_id'"));
        $thereIsAmount = false;
        global $productQuantity;
        for ($row = 0; $row < $count; $row++) {
            $productQuantity[request("item_lines.'product_id'.".$row)] = 0;
        }
        for ($row = 0; $row < $count; $row++) {
            $productExists = Product::where('id', request("item_lines.'product_id'.".$row))->exists();
            if (!$productExists) {
                $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                    ': Product is invalid.');
            }
            if (is_numeric(request("item_lines.'amount'.".$row)) &&
            request("item_lines.'amount'.".$row) > 0) {
                $thereIsAmount = true;
            }
            $this->validateItemQuantity($validator, $row);
            $this->validateItemAmount($validator, $row, $productExists);
            $this->validateItemTax($validator, $row, $productExists);
        }
        if (!$thereIsAmount) {
            $validator->errors()->add(
                'item_lines',
                'Item lines: There should be at least one positive amount.'
            );
        }
        $this->valProdQuanti($validator, $count);
    }
    public function validateItemQuantity($validator, $row)
    {
        global $productQuantity;
        if (!is_null(request("item_lines.'quantity'.".$row))) {
            if (is_numeric(request("item_lines.'quantity'.".$row))) {
                if (request("item_lines.'quantity'.".$row) < 0.001) {
                    $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                        ': Quantity must be at least 0.001.');
                } else {
                    $productQuantity[request("item_lines.'product_id'.".$row)]
                        += request("item_lines.'quantity'.".$row);
                }
            } else {
                $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                    ': Quantity must be a number.');
            }
        }
    }
    public function validateItemAmount($validator, $row, $productExists)
    {
        $product = new Product();
        if ($productExists) {
            $product = Product::where('id', request("item_lines.'product_id'.".$row))->firstOrFail();
        }
        $createCreditNote = new CreateCreditNote();
        $itemAmounts = $createCreditNote->determineAmounts(
            request("invoice_id"),
            request("item_lines.'product_id'.".$row),
            request("item_lines.'quantity'.".$row)
        );
        if (is_null(request("item_lines.'amount'.".$row))) {
            if (!is_null(request("item_lines.'quantity'.".$row))) {
                $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                    ': Amount required.');
            }
        } else {
            if (is_numeric(request("item_lines.'amount'.".$row))) {
                if ($product->track_quantity) {
                    if (request("item_lines.'amount'.".$row) != $itemAmounts['amount']) {
                        $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                            ': Amount must be ' . $itemAmounts['amount'] . '.');
                    }
                } else {
                    if (request("item_lines.'amount'.".$row) > $itemAmounts['amount']) {
                        $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                            ': Amount must not exceed ' . $itemAmounts['amount'] . '.');
                    }
                    if (request("item_lines.'amount'.".$row) < 0) {
                        $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                            ': Amount must be a positive number.');
                    }
                }
            } else {
                $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                    ': Amount must be a number.');
            }
        }
    }
    public function validateItemTax($validator, $row, $productExists)
    {
        $product = new Product();
        if ($productExists) {
            $product = Product::where('id', request("item_lines.'product_id'.".$row))->firstOrFail();
        }
        $createCreditNote = new CreateCreditNote();
        $itemAmounts = $createCreditNote->determineAmounts(
            request("invoice_id"),
            request("item_lines.'product_id'.".$row),
            request("item_lines.'quantity'.".$row)
        );
        $maxTax = ( $itemAmounts['tax_unreturned'] / $itemAmounts['amount_unreturned'] )
            * request("item_lines.'amount'.".$row);
        if (is_null(request("item_lines.'output_tax'.".$row))) {
            $this->validateTaxAmount($validator, $row, $product, $itemAmounts, $maxTax);
        } else {
            if (is_numeric(request("item_lines.'output_tax'.".$row))) {
                $this->validateTaxAmount($validator, $row, $product, $itemAmounts, $maxTax);
            } else {
                $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                    ': Tax must be a number.');
            }
        }
    }
    public function validateTaxAmount($validator, $row, $product, $itemAmounts, $maxTax)
    {
        if ($product->track_quantity) {
            if (request("item_lines.'output_tax'.".$row) != $itemAmounts['tax']) {
                $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                    ': Tax must be ' . $itemAmounts['tax'] . '.');
            }
        } else {
            if (request("item_lines.'output_tax'.".$row) != $maxTax) {
                $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                    ': Tax must be ' . $maxTax . '.');
            }
        }
    }
    public function valProdQuanti($validator, $count)
    {
        global $productQuantity;
        for ($row = 0; $row < $count; $row++) {
            $productExists = Product::where('id', request("item_lines.'product_id'.".$row))->exists();
            if ($productExists) {
                $product = Product::where('id', request("item_lines.'product_id'.".$row))->firstOrFail();
                if ($product->track_quantity) {
                    $createCreditNote = new CreateCreditNote();
                    $itemAmounts = $createCreditNote->determineAmounts(
                        request("invoice_id"),
                        request("item_lines.'product_id'.".$row),
                        request("item_lines.'quantity'.".$row)
                    );
                    if ($productQuantity[request("item_lines.'product_id'.".$row)]
                        > $itemAmounts['quantity_unreturned']) {
                        $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                            ': Total quantity for this product must not exceed '
                                . $itemAmounts['quantity_unreturned'] . '.');
                    }
                }
            }
        }
    }
}
