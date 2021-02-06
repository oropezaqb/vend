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
use App\Bill;
use App\BillItemLine;
use App\BillCategoryLine;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */

class CreateSupplierCredit
{
    public function determineCAmounts($purchasableDoc, $docId, $accountId, $supplierCreditId)
    {
        $company = \Auth::user()->currentCompany->company;
        $document = null;
        $amounts = array();
        switch ($purchasableDoc) {
            case 'Bill':
                $document = Bill::where('company_id', $company->id)->where('id', $docId)->first();
                $amounts = $this->determineCBillAmounts($document, $purchasableDoc, $docId, $accountId, $supplierCreditId);
                break;
            case 'Cheque':
                $document = Cheque::where('company_id', $company->id)->where('id', $docId)->first();
                $amounts = $this->determineCChequeAmounts($document, $purchasableDoc, $docId, $accountId, $supplierCreditId);
                break;
            default:
                $document = null;
        }
        return $amounts;
    }
    public function determineCBillAmounts($document, $purchasableDoc, $docId, $accountId, $supplierCreditId)
    {
        $account = Account::find($accountId);
        $amountPurchased = BillCategoryLine::where('bill_id', $document->id)
            ->where('account_id', $account->id)->sum('amount');
        $taxPurchased = BillCategoryLine::where('bill_id', $document->id)
            ->where('account_id', $account->id)->sum('input_tax');
        $amtReturnForThisSC = 0;
        if (!is_null($supplierCreditId)) {
            $amtReturnForThisSC = SupplierCreditCLine::where('supplier_credit_id', $supplierCreditId)
                ->where('purchasable_type', 'App\Models\Bill')
                ->where('purchasable_id', $docId)
                ->where('account_id', $account->id)->sum('amount');
        }
        $amountReturned = SupplierCreditCLine::where('purchasable_type', 'App\Models\Bill')
            ->where('purchasable_id', $docId)
            ->where('account_id', $account->id)->sum('amount');
        $taxReturnForThisSC = 0;
        if (!is_null($supplierCreditId)) {
            $taxReturnForThisSC = SupplierCreditCLine::where('supplier_credit_id', $supplierCreditId)
                ->where('purchasable_type', 'App\Models\Bill')
                ->where('purchasable_id', $docId)
                ->where('account_id', $account->id)->sum('input_tax');
        }
        $taxReturned = SupplierCreditCLine::where('purchasable_type', 'App\Models\Bill')
            ->where('purchasable_id', $docId)
            ->where('account_id', $account->id)->sum('input_tax');
        $amountUnreturned = $amountPurchased - ($amountReturned - $amtReturnForThisSC);
        $taxUnreturned = $taxPurchased - ($taxReturned - $taxReturnForThisSC);
        $amounts = array();
        $amounts['amount_unreturned'] = $amountUnreturned;
        $amounts['tax_unreturned'] = $taxUnreturned;
        $amounts['amount'] = $amountUnreturned;
        $amounts['tax'] = $taxUnreturned;
        return $amounts;
    }
    public function determineAmounts($purchasableDoc, $docId, $productId, $quantity, $supplierCreditId)
    {
        $company = \Auth::user()->currentCompany->company;
        $document = null;
        $amounts = array();
        switch ($purchasableDoc) {
            case 'Bill':
                $document = Bill::where('company_id', $company->id)->where('id', $docId)->first();
                $amounts = $this->determineBillAmounts($document, $purchasableDoc, $docId, $productId, $quantity, $supplierCreditId);
                break;
            case 'Cheque':
                $document = Cheque::where('company_id', $company->id)->where('id', $docId)->first();
                $amounts = $this->determineChequeAmounts($document, $purchasableDoc, $docId, $productId, $quantity, $supplierCreditId);
                break;
            default:
                $document = null;
        }
        return $amounts;
    }
    public function determineBillAmounts($document, $purchasableDoc, $docId, $productId, $quantity, $supplierCreditId)
    {
        $product = Product::find($productId);
        $quantityPurchased = BillItemLine::where('bill_id', $document->id)
            ->where('product_id', $product->id)->sum('quantity');
        $amountPurchased = BillItemLine::where('bill_id', $document->id)
            ->where('product_id', $product->id)->sum('amount');
        $taxPurchased = BillItemLine::where('bill_id', $document->id)
            ->where('product_id', $product->id)->sum('input_tax');
        $qtyReturnForThisSC = 0;
        if (!is_null($supplierCreditId)) {
            $qtyReturnForThisSC = SupplierCreditILine::where('supplier_credit_id', $supplierCreditId)
                ->where('purchasable_type', 'App\Models\Bill')
                ->where('purchasable_id', $docId)
                ->where('product_id', $product->id)->sum('quantity');
        }
        $quantityReturned = SupplierCreditILine::where('purchasable_type', 'App\Models\Bill')
            ->where('purchasable_id', $docId)
            ->where('product_id', $product->id)->sum('quantity');
        $amtReturnForThisSC = 0;
        if (!is_null($supplierCreditId)) {
            $amtReturnForThisSC = SupplierCreditILine::where('supplier_credit_id', $supplierCreditId)
                ->where('purchasable_type', 'App\Models\Bill')
                ->where('purchasable_id', $docId)
                ->where('product_id', $product->id)->sum('amount');
        }
        $amountReturned = SupplierCreditILine::where('purchasable_type', 'App\Models\Bill')
            ->where('purchasable_id', $docId)
            ->where('product_id', $product->id)->sum('amount');
        $taxReturnForThisSC = 0;
        if (!is_null($supplierCreditId)) {
            $taxReturnForThisSC = SupplierCreditILine::where('supplier_credit_id', $supplierCreditId)
                ->where('purchasable_type', 'App\Models\Bill')
                ->where('purchasable_id', $docId)
                ->where('product_id', $product->id)->sum('input_tax');
        }
        $taxReturned = SupplierCreditILine::where('purchasable_type', 'App\Models\Bill')
            ->where('purchasable_id', $docId)
            ->where('product_id', $product->id)->sum('input_tax');
        $quantityUnreturned = $quantityPurchased - ($quantityReturned - $qtyReturnForThisSC);
        $amountUnreturned = $amountPurchased - ($amountReturned - $amtReturnForThisSC);
        $taxUnreturned = $taxPurchased - ($taxReturned - $taxReturnForThisSC);
        $amounts = array();
        $amounts['amount'] = 0;
        $amounts['tax'] = 0;
        $amounts['amount_unreturned'] = $amountUnreturned;
        $amounts['tax_unreturned'] = $taxUnreturned;
        $amounts['quantity_unreturned'] = $quantityUnreturned;
        if (($quantity > 0) && ($quantity < $quantityUnreturned)) {
            $amounts['amount'] = round(($amountUnreturned / $quantityUnreturned) * $quantity, 2);
            $amounts['tax'] = round(($taxUnreturned / $quantityUnreturned) * $quantity, 2);
        }
        if ($quantity == $quantityUnreturned) {
            $amounts['amount'] = $amountUnreturned;
            $amounts['tax'] = $taxUnreturned;
        }
        return $amounts;
    }
    public function updateLines($supplierCredit, $document)
    {
        if (!is_null(request("category_lines.'account_id'"))) {
            $count = count(request("category_lines.'account_id'"));
            for ($row = 0; $row < $count; $row++) {
                $inputTax = 0;
                if (!is_null(request("category_lines.'input_tax'.".$row))) {
                    $inputTax = request("category_lines.'input_tax'.".$row);
                }
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
        if (!is_null(request("item_lines.'product_id'"))) {
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
}
