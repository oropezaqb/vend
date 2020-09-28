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

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */

class CreateInvoice
{
    public function recordSales($invoice)
    {
        if (!is_null(request("item_lines.'product_id'"))) {
            $count = count(request("item_lines.'product_id'"));
            for ($row = 0; $row < $count; $row++) {
                $product = Product::find(request("item_lines.'product_id'.".$row));
                if ($product->track_quantity) {
                    $numberRecorded = 0;
                    do {
                        $company = \Auth::user()->currentCompany->company;
                        $purchase = $this->determinePurchaseSold($company, $product);
                        $numberUnrecorded = request("item_lines.'quantity'.".$row) - $numberRecorded;
                        $quantity = $this->determineQuantitySold($company, $purchase, $numberUnrecorded);
                        $amount = $this->determineAmountSold($company, $purchase, $numberUnrecorded);
                        $sale = new Sale([
                            'company_id' => $company->id,
                            'purchase_id' => $purchase->id,
                            'date' => request('invoice_date'),
                            'product_id' => $product->id,
                            'quantity' => $quantity,
                            'amount' => $amount
                        ]);
                        $invoice->sales()->save($sale);
                        $numberRecorded += $quantity;
                    } while ($numberRecorded < request("item_lines.'quantity'.".$row));
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
    public function recordJournalEntry($invoice)
    {
        $company = \Auth::user()->currentCompany->company;
        $document = Document::firstOrCreate(['name' => 'Invoice', 'company_id' => $company->id]);
        $receivableAccount = Account::where('title', 'Accounts Receivable')->firstOrFail();
        $taxAccount = Account::where('title', 'Output VAT')->firstOrFail();
        $customer = Customer::all()->find(request('customer_id'));
        $receivableSubsidiary = SubsidiaryLedger::where('name', $customer->name)
            ->firstOrCreate(['name' => $customer->name, 'company_id' => $company->id]);
        $journalEntry = new JournalEntry([
            'company_id' => $company->id,
            'date' => request('invoice_date'),
            'document_type_id' => $document->id,
            'explanation' => 'To record sale of goods on account.'
        ]);
        $invoice->journalEntries()->save($journalEntry);
        $receivableAmount = 0;
        $taxAmount = 0;
        if (!is_null(request("item_lines.'product_id'"))) {
            $count = count(request("item_lines.'product_id'"));
            for ($row = 0; $row < $count; $row++) {
                $inputTax = 0;
                if (!is_null(request("item_lines.'input_tax'.".$row))) {
                    $inputTax = request("item_lines.'input_tax'.".$row);
                }
                $product = Product::find(request("item_lines.'product_id'.".$row));
                $debit = -request("item_lines.'amount'.".$row);
                $posting = new Posting([
                    'company_id' => $company->id,
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $product->incomeAccount->id,
                    'debit' => $debit
                ]);
                $posting->save();
                $receivableAmount += request("item_lines.'amount'.".$row) + $inputTax;
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
            'debit' => $receivableAmount,
            'subsidiary_ledger_id' => $receivableSubsidiary->id
        ]);
        $posting->save();
        $this->recordCost($invoice, $company, $journalEntry);
    }
    public function recordCost($invoice, $company, $journalEntry)
    {
        foreach ($invoice->sales as $sale) {
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
}
