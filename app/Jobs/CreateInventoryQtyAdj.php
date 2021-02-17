<?php

namespace App\Jobs;

use App\Account;
use App\Product;
use App\Document;
use App\SubsidiaryLedger;
use App\Transaction;
use App\InventoryQtyAdjLine;
use App\InvoiceItemLine;
use App\SalesReturn;
use App\Invoice;
use App\JournalEntry;
use App\Posting;
use App\Purchase;
use App\Sale;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */

class CreateInventoryQtyAdj
{
    public function updateLines($inventoryQtyAdj)
    {
        if (!is_null(request("item_lines.'product_id'"))) {
            $count = count(request("item_lines.'product_id'"));
            for ($row = 0; $row < $count; $row++) {
                $itemLine = new InventoryQtyAdjLine([
                    'inventory_qty_adj_id' => $inventoryQtyAdj->id,
                    'product_id' => request("item_lines.'product_id'.".$row),
                    'description' => request("item_lines.'description'.".$row),
                    'change_in_qty' => request("item_lines.'change_in_qty'.".$row)
                ]);
                $itemLine->save();
            }
        }
    }
    public function updateSalesAndPurchases($inventoryQtyAdj)
    {
        foreach($inventoryQtyAdj->lines as $line)
        {
            if($line->change_in_qty > 0)
            {
                $this->recordPurchase($inventoryQtyAdj, $line);
            }
            if($line->change_in_qty < 0)
            {
                $this->recordSales($inventoryQtyAdj, $line);
            }
        }
    }
    public function recordPurchase($inventoryQtyAdj, $line)
    {
        $company = \Auth::user()->currentCompany->company;
        $amount = $this->determinePurchaseAmount($line, $company);
        $purchase = new Purchase([
            'company_id' => $company->id,
            'date' => request('date'),
            'product_id' => $line->product_id,
            'quantity' => $line->change_in_qty,
            'amount' => $amount
        ]);
        $inventoryQtyAdj->purchases()->save($purchase);
    }
    public function determinePurchaseAmount($line, $company)
    {
        $product = Product::find($line->product_id);
        $allPurchases = Purchase::where('company_id', $company->id)->where('product_id', $product->id)->get();
        $purchases = $allPurchases->sortBy('date');
        foreach ($purchases as $purchase) {
            $numberSold = Sale::where('company_id', $company->id)->where('purchase_id', $purchase->id)->sum('quantity');
            if ($numberSold < $purchase->quantity) {
                $amount = round(($purchase->amount / $purchase->quantity) * $line->change_in_qty, 2);
                return $amount;
            }
        }
    }
    public function recordSales($inventoryQtyAdj, $line)
    {
        $product = Product::find($line->product_id);
        if ($product->track_quantity) {
            $numberRecorded = 0;
            $changeInQty = $line->change_in_qty * -1;
            do {
                $company = \Auth::user()->currentCompany->company;
                $purchase = $this->determinePurchaseSold($company, $product);
                if (is_object($purchase)) {
                    $numberUnrecorded = $changeInQty - $numberRecorded;
                    $quantity = $this->determineQuantitySold($company, $purchase, $numberUnrecorded);
                    $amount = $this->determineAmountSold($company, $purchase, $numberUnrecorded);
                    $sale = new Sale([
                        'company_id' => $company->id,
                        'purchase_id' => $purchase->id,
                        'date' => $inventoryQtyAdj->date,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'amount' => $amount
                    ]);
                    $inventoryQtyAdj->sales()->save($sale);
                    $numberRecorded += $quantity;
                } else {
                    break;
                }
            } while ($numberRecorded < $changeInQty);
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
    public function recordJournalEntry($inventoryQtyAdj)
    {
        $company = \Auth::user()->currentCompany->company;
        $document = Document::firstOrCreate(['name' => 'Inventory Qty Adjustment', 'company_id' => $company->id]);
        $journalEntry = new JournalEntry([
            'company_id' => $company->id,
            'date' => request('date'),
            'document_type_id' => $document->id,
            'document_number' => request('number'),
            'explanation' => 'To record inventory quantity adjustment.'
        ]);
        $inventoryQtyAdj->journalEntry()->save($journalEntry);
        foreach($inventoryQtyAdj->purchases as $purchase)
        {
            $this->recordPurchasePosting($inventoryQtyAdj, $journalEntry, $purchase);
        }
        foreach($inventoryQtyAdj->sales as $sale)
        {
            $this->recordSalePosting($inventoryQtyAdj, $journalEntry, $sale);
        }
    }
    public function recordPurchasePosting($inventoryQtyAdj, $journalEntry, $purchase)
    {
        $company = \Auth::user()->currentCompany->company;
        $product = Product::find($purchase->product_id);
        $account = Account::find(request('account_id'));
        $inventoryPosting = new Posting([
            'company_id' => $company->id,
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $product->inventoryAccount->id,
            'debit' => $purchase->amount
        ]);
        $inventoryPosting->save();
        $gainPosting = new Posting([
            'company_id' => $company->id,
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $account->id,
            'debit' => -$purchase->amount
        ]);
        $gainPosting->save();
    }
    public function recordSalePosting($inventoryQtyAdj, $journalEntry, $sale)
    {
        $company = \Auth::user()->currentCompany->company;
        $product = Product::find($sale->product_id);
        $account = Account::find(request('account_id'));
        $lossPosting = new Posting([
            'company_id' => $company->id,
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $account->id,
            'debit' => $sale->amount
        ]);
        $lossPosting->save();
        $inventoryPosting = new Posting([
            'company_id' => $company->id,
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $product->inventoryAccount->id,
            'debit' => -$sale->amount
        ]);
        $inventoryPosting->save();
    }
    public function recordTransaction($inventoryQtyAdj)
    {
        $company = \Auth::user()->currentCompany->company;
        $transaction = new Transaction([
            'company_id' => $company->id,
            'type' => 'inventory_qty_adj',
            'date' => request('date')
        ]);
        $inventoryQtyAdj->transaction()->save($transaction);
    }
    public function deleteInventoryQtyAdj($inventoryQtyAdj)
    {
        $inventoryQtyAdj->journalEntry->delete();
        $inventoryQtyAdj->transaction->delete();
        //foreach ($inventoryQtyAdj->salesReturns as $salesReturn) {
        //    $salesReturn->delete();
        //}
        foreach ($inventoryQtyAdj->purchases as $purchase) {
            $purchase->delete();
        }
    }
}