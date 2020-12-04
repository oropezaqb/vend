<?php

namespace App\Jobs;

use App\Customer;
use App\Account;
use App\Product;
use App\Purchase;
use App\Sale;
use App\Document;
use App\JournalEntry;
use App\Posting;
use App\SubsidiaryLedger;
use App\Transaction;
use App\SalesReceiptItemLine;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */

class CreateSalesReceipt
{
    public function recordSales($salesReceipt, $input)
    {
        $count = count($input['item_lines']["'product_id'"], 1);
        if ($count > 0) {
            for ($row = 0; $row < $count; $row++) {
                $product = Product::find($input['item_lines']["'product_id'"][$row]);
                if ($product->track_quantity) {
                    $numberRecorded = 0;
                    do {
                        $company = \Auth::user()->currentCompany->company;
                        $purchase = $this->determinePurchaseSold($company, $product);
                        if (is_object($purchase)) {
                            $numberUnrecorded = $input['item_lines']["'quantity'"][$row] - $numberRecorded;
                            $quantity = $this->determineQuantitySold($company, $purchase, $numberUnrecorded);
                            $amount = $this->determineAmountSold($company, $purchase, $numberUnrecorded);
                            $sale = new Sale([
                                'company_id' => $company->id,
                                'purchase_id' => $purchase->id,
                                'date' => $input['date'],
                                'product_id' => $product->id,
                                'quantity' => $quantity,
                                'amount' => $amount
                            ]);
                            $salesReceipt->sales()->save($sale);
                            $numberRecorded += $quantity;
                        } else {
                            break;
                        }
                    } while ($numberRecorded < $input['item_lines']["'quantity'"][$row]);
                }
            }
        }
    }
    public function determinePurchaseSold($company, $product)
    {
        $allPurchases = Purchase::where('company_id', $company->id)->where('product_id', $product->id)->get();
        $purchases = $allPurchases->sortBy('date');
        foreach ($purchases as $purchase) {
            $numberSold = Sale::where('company_id', $company->id)->where('purchase_id', $purchase->id)->sum('quantity');
            if ($numberSold < $purchase->quantity) {
                return $purchase;
            }
        }
    }
    public function determineQuantitySold($company, $purchase, $numberUnrecorded)
    {
        $numberSold = Sale::where('company_id', $company->id)->where('purchase_id', $purchase->id)->sum('quantity');
        $numberUnsold = $purchase->quantity - $numberSold;
        if ($numberUnrecorded < $numberUnsold) {
            return $numberUnrecorded;
        } else {
            return $numberUnsold;
        }
    }
    public function determineAmountSold($company, $purchase, $numberUnrecorded)
    {
        $numberSold = Sale::where('company_id', $company->id)->where('purchase_id', $purchase->id)->sum('quantity');
        $numberUnsold = $purchase->quantity - $numberSold;
        $amountSold = Sale::where('company_id', $company->id)->where('purchase_id', $purchase->id)->sum('amount');
        $amountUnsold = $purchase->amount - $amountSold;
        if ($numberUnrecorded < $numberUnsold) {
            $costOfSales = round($amountUnsold / $numberUnsold * $numberUnrecorded, 2);
            return $costOfSales;
        } else {
            return $amountUnsold;
        }
    }
    public function recordJournalEntry($salesReceipt, $input)
    {
        $company = \Auth::user()->currentCompany->company;
        $document = Document::firstOrCreate(['name' => 'Sales Receipt', 'company_id' => $company->id]);
        $receivableAccount = Account::all()->find($input['account_id']);
        $taxAccount = Account::where('title', 'Output VAT')->firstOrFail();
        $journalEntry = new JournalEntry([
            'company_id' => $company->id,
            'date' => $input['date'],
            'document_type_id' => $document->id,
            'document_number' => $input['number'],
            'explanation' => 'To record sale of goods for cash.'
        ]);
        $salesReceipt->journalEntry()->save($journalEntry);
        $receivableAmount = 0;
        $taxAmount = 0;
        $count = count($input['item_lines']["'product_id'"]);
        if ($count > 0) {
            for ($row = 0; $row < $count; $row++) {
                $inputTax = 0;
                if (!is_null($input['item_lines']["'output_tax'"][$row])) {
                    $inputTax = $input['item_lines']["'output_tax'"][$row];
                }
                $product = Product::find($input['item_lines']["'product_id'"][$row]);
                $debit = -$input['item_lines']["'amount'"][$row];
                $posting = new Posting([
                    'company_id' => $company->id,
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $product->incomeAccount->id,
                    'debit' => $debit
                ]);
                $posting->save();
                $receivableAmount += $input['item_lines']["'amount'"][$row] + $inputTax;
                $taxAmount -= $inputTax;
            }
        }
        if ($taxAmount != 0) {
            $posting = new Posting([
                'company_id' => $company->id,
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $taxAccount->id,
                'debit' => $taxAmount
            ]);
            $posting->save();
        }
        $posting = new Posting([
            'company_id' => $company->id,
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $receivableAccount->id,
            'debit' => $receivableAmount
        ]);
        $posting->save();
        $this->recordCost($salesReceipt, $company, $journalEntry);
    }
    public function recordCost($salesReceipt, $company, $journalEntry)
    {
        foreach ($salesReceipt->sales as $sale) {
            $product = Product::find($sale->product_id);
            $posting = new Posting([
                'company_id' => $company->id,
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $product->expenseAccount->id,
                'debit' => $sale->amount
            ]);
            $posting->save();
            $debit = -$sale->amount;
            $inventoryPosting = new Posting([
                'company_id' => $company->id,
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $product->inventoryAccount->id,
                'debit' => $debit
            ]);
            $inventoryPosting->save();
        }
    }
    public function recordTransaction($salesReceipt)
    {
        $company = \Auth::user()->currentCompany->company;
        $transaction = new Transaction([
            'company_id' => $company->id,
            'type' => 'sale',
            'date' => request('date')
        ]);
        $salesReceipt->transaction()->save($transaction);
    }
    public function updateSales($salesForUpdate)
    {
        foreach ($salesForUpdate as $saleForUpdate) {
            $transactions = Transaction::all();
            $transaction = $transactions->find($saleForUpdate->id);
            $salesReceipt = $transaction->transactable;
            if (is_object($salesReceipt->journalEntry)) {
                foreach ($salesReceipt->journalEntry->postings as $posting) {
                    $posting->delete();
                }
                $salesReceipt->journalEntry->delete();
            }
            if (is_object($salesReceipt->sales)) {
                $sales = $salesReceipt->sales;
                foreach ($sales as $sale) {
                    $sale->delete();
                }
            }
        }
        foreach ($salesForUpdate as $saleForUpdate) {
            $transactions = Transaction::all();
            $transaction = $transactions->find($saleForUpdate->id);
            $salesReceipt = $transaction->transactable;
            $input = array();
            $row = 0;
            $input['customer_id'] = $salesReceipt->customer_id;
            $input['date'] = $salesReceipt->date;
            $input['number'] = $salesReceipt->number;
            $input['account_id'] = $salesReceipt->account_id;
            foreach ($salesReceipt->itemLines as $itemLine) {
                $input['item_lines']["'product_id'"][$row] = $itemLine->product_id;
                $input['item_lines']["'description'"][$row] = $itemLine->description;
                $input['item_lines']["'quantity'"][$row] = $itemLine->quantity;
                $input['item_lines']["'amount'"][$row] = $itemLine->amount;
                $input['item_lines']["'output_tax'"][$row] = $itemLine->output_tax;
                $row += 1;
            }
            $createSalesReceipt = new CreateSalesReceipt();
            $createSalesReceipt->recordSales($salesReceipt, $input);
            $createSalesReceipt->recordJournalEntry($salesReceipt, $input);
        }
    }
    public function updateLines($salesReceipt)
    {
        if (!is_null(request("item_lines.'product_id'"))) {
            $count = count(request("item_lines.'product_id'"));
            for ($row = 0; $row < $count; $row++) {
                $outputTax = 0;
                if (!is_null(request("item_lines.'output_tax'.".$row))) {
                    $outputTax = request("item_lines.'output_tax'.".$row);
                }
                $itemLine = new SalesReceiptItemLine([
                    'sales_receipt_id' => $salesReceipt->id,
                    'product_id' => request("item_lines.'product_id'.".$row),
                    'description' => request("item_lines.'description'.".$row),
                    'quantity' => request("item_lines.'quantity'.".$row),
                    'amount' => request("item_lines.'amount'.".$row),
                    'output_tax' => $outputTax
                ]);
                $itemLine->save();
            }
        }
    }
    public function deleteSalesReceiptDetails($salesReceipt)
    {
        foreach ($salesReceipt->itemLines as $itemLine) {
            $itemLine->delete();
        }
        foreach ($salesReceipt->sales as $sale) {
            $sale->delete();
        }
    }
}
