<?php

namespace App\Jobs;

use Illuminate\Foundation\Http\FormRequest;
use App\Product;
use App\Account;
use App\Bill;
use App\Jobs\CreateSupplierCredit;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */

class ValidateSCItemLines
{
    public $productQuantity = array();
    public function validate($validator, $thereIsAmount)
    {
        $count = count(request("item_lines.'product_id'"));
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
        return $thereIsAmount;
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
