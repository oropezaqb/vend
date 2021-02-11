<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Product;
use App\Account;
use App\Bill;
use App\Jobs\CreateSupplierCredit;
use App\Jobs\ValidateSCItemLines;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */

class StoreSupplierCredit extends FormRequest
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
            "category_lines.'amount'.*" => ['sometimes', 'numeric', 'nullable'],
            "category_lines.'input_tax'.*" => ['sometimes', 'numeric', 'nullable'],
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
            //$docId = request('doc_id');
            switch ($purchasableDoc) {
                case 'Bill':
                    $bills = Bill::where('company_id', $company->id)->get();
                    $this->validateDocument($validator, $bills);
                    break;
                case 'Cheque':
                    $cheques = Cheque::where('company_id', $company->id)->get();
                    $this->validateDocument($validator, $cheques);
                    break;
                default:
                    $validator->errors()->add('document', 'Invalid document type.');
            }
            if (!is_int(filter_var(request('number'), FILTER_VALIDATE_INT))) {
                $validator->errors()->add('number', 'Supplier credit number must be an integer.');
            }
            if (is_null(request("category_lines.'account_id'")) && is_null(request("item_lines.'product_id'"))) {
                $validator->errors()->add(
                    'lines',
                    'Please select a valid document with corresponding accounts or products.'
                );
            }
            if (!is_null(request("category_lines.'account_id'"))) {
                $this->validateCategoryLines($validator);
            }
            if (!is_null(request("item_lines.'product_id'"))) {
                $validateSCItemLines = new ValidateSCItemLines();
                $thereIsAmount = $validateSCItemLines->validate($validator, $thereIsAmount);
            }
            if (!$thereIsAmount) {
                $validator->errors()->add(
                    'item_lines',
                    'Lines: There should be at least one positive amount.'
                );
            }
        });
    }
    public function validateDocument($validator, $collection)
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
}
