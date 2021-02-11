<?php

namespace App\Jobs;

use App\Customer;
use App\Account;
use App\Product;
use App\Document;
use App\SubsidiaryLedger;
use App\Transaction;
use App\SupplierCreditCLine;
use App\SupplierCreditILine;
use App\InvoiceItemLine;
use App\SalesReturn;
use App\Invoice;
use App\JournalEntry;
use App\Posting;
use App\Purchase;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */

class SaveSCLines
{
    public function updateLines($supplierCredit, $document)
    {
        if (!is_null(request("category_lines.'account_id'"))) {
            $this->updateCLines($supplierCredit, $document);
        }
        if (!is_null(request("item_lines.'product_id'"))) {
            $this->updateILines($supplierCredit, $document);
        }
    }
    public function updateCLines($supplierCredit, $document)
    {
        $count = count(request("category_lines.'account_id'"));
        for ($row = 0; $row < $count; $row++) {
            $inputTax = 0;
            if (!is_null(request("category_lines.'input_tax'.".$row))) {
                $inputTax = request("category_lines.'input_tax'.".$row);
            }
            if (!is_null(request("category_lines.'amount'.".$row)) &&
                is_numeric(request("category_lines.'amount'.".$row))) {
                $categoryLine = new SupplierCreditCLine([
                    'supplier_credit_id' => $supplierCredit->id,
                    'account_id' => request("category_lines.'account_id'.".$row),
                    'description' => request("category_lines.'description'.".$row),
                    'amount' => request("category_lines.'amount'.".$row),
                    'input_tax' => $inputTax
                ]);
                $document->supplierCreditCLine()->save($categoryLine);
            }
        }
    }
    public function updateILines($supplierCredit, $document)
    {
        $count = count(request("item_lines.'product_id'"));
        for ($row = 0; $row < $count; $row++) {
            $inputTax = 0;
            if (!is_null(request("item_lines.'input_tax'.".$row))) {
                $inputTax = request("item_lines.'input_tax'.".$row);
            }
            if (!is_null(request("item_lines.'amount'.".$row)) &&
                is_numeric(request("item_lines.'amount'.".$row))) {
                if (request("item_lines.'amount'.".$row) > 0) {
                    $itemLine = new SupplierCreditILine([
                        'supplier_credit_id' => $supplierCredit->id,
                        'product_id' => request("item_lines.'product_id'.".$row),
                        'description' => request("item_lines.'description'.".$row),
                        'quantity' => request("item_lines.'quantity'.".$row),
                        'amount' => request("item_lines.'amount'.".$row),
                        'input_tax' => $inputTax
                    ]);
                    $document->supplierCreditILine()->save($itemLine);
                }
            }
        }
    }
}
