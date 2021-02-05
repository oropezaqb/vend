<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Product;
use App\Account;
use App\Bill;
use App\Jobs\CreateSupplierCredit;

class StoreSupplierCredit extends FormRequest
{
    public $productQuantity = array();
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
            'purchasable_doc.required' => 'The document type is required.',
            'doc_id.required' => 'The document number is required.',
            'number.min' => 'The Supplier Credit number must be positive.',
            "category_lines.'account_id'.*.exists" =>
                'Some account titles are invalid.',
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
            'purchasable_doc' => ['required'],
            'doc_id' => ['required'],
            'date' => ['required', 'date'],
            'number' => ['required', 'numeric', 'min:1'],
            "category_lines.'account_id'.*" => [
                'required',
                'exists:App\Account,id'
            ],
            "item_lines.'amount'.*" => ['sometimes', 'numeric', 'nullable'],
            "item_lines.'input_tax'.*" => ['sometimes', 'numeric', 'nullable'],
            "item_lines.'product_id'.*" => [
                'required',
                'exists:App\Product,id'
            ],
            "item_lines.'quantity'.*" => ['sometimes', 'numeric', 'nullable'],
            "item_lines.'amount'.*" => ['sometimes', 'numeric', 'nullable'],
            "item_lines.'input_tax'.*" => ['sometimes', 'numeric', 'nullable'],
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            global $thereIsAmount;
            $company = \Auth::user()->currentCompany->company;
            $purchasableDoc = request('purchasable_doc');
            $docId = request('doc_id');
            $document = null;
            switch ($purchasableDoc) {
                case 'Bill':
                    $document = Bill::where('company_id', $company->id)->where('id', $docId)->first();
                    $bills = Bill::where('company_id', $company->id)->get();
                    $this->validateDocument($bills);
                    break;
                case 'Cheque':
                    $document = Cheque::where('company_id', $company->id)->where('id', $docId)->first();
                    $cheques = Cheque::where('company_id', $company->id)->get();
                    $this->validateDocument($cheques);
                    break;
                default:
                    $document = null;
            }
            if (!is_int(filter_var(request('number'), FILTER_VALIDATE_INT))) {
                $validator->errors()->add('number', 'Supplier credit number must be an integer.');
            }
            if (is_null(request("category_lines.'account_id'")) && is_null(request("item_lines.'product_id'"))) {
                $validator->errors()->add('lines', 'Please select a valid document with corresponding accounts or products.');
            }
            if (!is_null(request("category_lines.'account_id'"))) {
                $this->validateCategoryLines($validator);
            }
            if (!is_null(request("item_lines.'product_id'"))) {
                $this->validateItemLines($validator);
            }
            if (!$thereIsAmount) {
                $validator->errors()->add(
                    'item_lines',
                    'Lines: There should be at least one positive amount.'
                );
            }
        });
    }
    public function validateDocument($collection)
    {
        if (!$collection->contains('id', request('doc_id'))) {
            $validator->errors()->add('doc_id', 'Document type and number are invalid.');
        }
    }
    public function validateCategoryLines($validator)
    {
        global $thereIsAmount;
        $count = count(request("category_lines.'account_id'"));
        for ($row = 0; $row < $count; $row++) {
            if (!Account::where('id', request("category_lines.'account_id'.".$row))->exists()) {
                $validator->errors()->add('category_lines', 'Category line ' . ($row + 1) .
                    ': Account is invalid. Please choose among the recommended items.');
            }
            if (is_numeric(request("category_lines.'amount'.".$row)) &&
            request("category_lines.'amount'.".$row) > 0) {
                $thereIsAmount = true;
            }
            $this->validateCategoryAmount($validator, $row);
            $this->validateCategoryTax($validator, $row);
        }
    }
    public function validateCategoryAmount($validator, $row)
    {
        $createSupplierCredit = new CreateSupplierCredit();
        $supplierCreditId = null;
        if (!is_null(request('supplier_credit_id'))) {
            $supplierCreditId = request('supplier_credit_id');
        }
        $itemAmounts = $createSupplierCredit->determineCAmounts(
            request("purchasable_doc"),
            request("doc_id"),
            request("category_lines.'account_id'.".$row),
            $supplierCreditId
        );
        if (!is_null(request("category_lines.'amount'.".$row))) {
            if (is_numeric(request("category_lines.'amount'.".$row))) {
                if (request("category_lines.'amount'.".$row) > $itemAmounts['amount']) {
                    $validator->errors()->add('category_lines', 'Category line ' . ($row + 1) .
                        ': Amount must not exceed ' . $itemAmounts['amount'] . '.');
                }
                if (request("category_lines.'amount'.".$row) < 0) {
                    $validator->errors()->add('category_lines', 'Category line ' . ($row + 1) .
                        ': Amount must be a positive number.');
                }
            } else {
                $validator->errors()->add('category_lines', 'Category line ' . ($row + 1) .
                    ': Amount must be a number.');
            }
        }
    }
    public function validateCategoryTax($validator, $row)
    {
        $account = Account::find(request("category_lines.'account_id'.".$row));
        $createSupplierCredit = new CreateSupplierCredit();
        $supplierCreditId = null;
        if (!is_null(request('supplier_credit_id'))) {
            $supplierCreditId = request('supplier_credit_id');
        }
        $itemAmounts = $createSupplierCredit->determineCAmounts(
            request("purchasable_doc"),
            request("doc_id"),
            request("category_lines.'account_id'.".$row),
            $supplierCreditId
        );
        $maxTax = ( $itemAmounts['tax_unreturned'] / $itemAmounts['amount_unreturned'] )
            * request("category_lines.'amount'.".$row);
        if (is_null(request("category_lines.'input_tax'.".$row))) {
            $this->validateCTaxAmount($validator, $row, $account, $itemAmounts, $maxTax);
        } else {
            if (is_numeric(request("category_lines.'input_tax'.".$row))) {
                $this->validateCTaxAmount($validator, $row, $account, $itemAmounts, $maxTax);
            } else {
                $validator->errors()->add('category_lines', 'Category line ' . ($row + 1) .
                    ': Tax must be a number.');
            }
        }
    }
    public function validateCTaxAmount($validator, $row, $product, $itemAmounts, $maxTax)
    {
        if (request("category_lines.'input_tax'.".$row) != $maxTax) {
            $validator->errors()->add('category_lines', 'Category line ' . ($row + 1) .
                ': Tax must be ' . $maxTax . '.');
        }
    }
    public function validateItemLines($validator)
    {
        $count = count(request("item_lines.'product_id'"));
        global $thereIsAmount;
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
        $createSupplierCredit = new CreateSupplierCredit();
        $supplierCreditId = null;
        if (!is_null(request('supplier_credit_id'))) {
            $supplierCreditId = request('supplier_credit_id');
        }
        $itemAmounts = $createSupplierCredit->determineAmounts(
            request("purchasable_doc"),
            request("doc_id"),
            request("item_lines.'product_id'.".$row),
            request("item_lines.'quantity'.".$row),
            $supplierCreditId
        );
        if (is_null(request("item_lines.'amount'.".$row))) {
            $this->checkIfProvided($validator, request("item_lines.'quantity'.".$row), $row);
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
    public function checkIfProvided($validator, $fld, $row)
    {
        if (!is_null($fld)) {
            $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                ': Amount required.');
        }
    }
    public function validateItemTax($validator, $row, $productExists)
    {
        $product = new Product();
        if ($productExists) {
            $product = Product::where('id', request("item_lines.'product_id'.".$row))->firstOrFail();
        }
        $createSupplierCredit = new CreateSupplierCredit();
        $supplierCreditId = null;
        if (!is_null(request('supplier_credit_id'))) {
            $supplierCreditId = request('supplier_credit_id');
        }
        $itemAmounts = $createSupplierCredit->determineAmounts(
            request("purchasable_doc"),
            request("doc_id"),
            request("item_lines.'product_id'.".$row),
            request("item_lines.'quantity'.".$row),
            $supplierCreditId
        );
        $maxTax = ( $itemAmounts['tax_unreturned'] / $itemAmounts['amount_unreturned'] )
            * request("item_lines.'amount'.".$row);
        if (is_null(request("item_lines.'input_tax'.".$row))) {
            $this->validateTaxAmount($validator, $row, $product, $itemAmounts, $maxTax);
        } else {
            if (is_numeric(request("item_lines.'input_tax'.".$row))) {
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
            if (request("item_lines.'input_tax'.".$row) != $itemAmounts['tax']) {
                $validator->errors()->add('item_lines', 'Item line ' . ($row + 1) .
                    ': Tax must be ' . $itemAmounts['tax'] . '.');
            }
        } else {
            if (request("item_lines.'input_tax'.".$row) != $maxTax) {
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
                    $createSupplierCredit = new CreateSupplierCredit();
                    $supplierCreditId = null;
                    if (!is_null(request('supplier_credit_id'))) {
                        $supplierCreditId = request('supplier_credit_id');
                    }
                    $itemAmounts = $createSupplierCredit->determineAmounts(
                        request("purchasable_doc"),
                        request("doc_id"),
                        request("item_lines.'product_id'.".$row),
                        request("item_lines.'quantity'.".$row),
                        $supplierCreditId
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
