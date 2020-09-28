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

class CreateInvoice
{
    public function recordSales($invoice)
    {
        if (!is_null(request("item_lines.'product_id'"))) {
            $count = count(request("item_lines.'product_id'"));
            for ($row = 0; $row < $count; $row++)
            {
                $product = Product::find(request("item_lines.'product_id'.".$row));
                if ($product->track_quantity) {
                    $numberRecorded = 0;
                    do
                    {
                        $company = \Auth::user()->currentCompany->company;
                        $purchase = $this->determinePurchaseSold($company, $product);
                        $numberUnrecorded = request("item_lines.'quantity'.".$row) - $numberRecorded;
                        $quantity = $this->determineQuantitySold($company, $product, $purchase, $row, $numberUnrecorded);
                        $amount = $this->determineAmountSold($company, $product, $purchase, $row, $numberUnrecorded);
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
        foreach ($purchases as $purchase)
        {
            $numberSold = Sale::where('company_id', $company->id)->where('purchase_id', $purchase->id)->sum('quantity');
            if ($numberSold < $purchase->quantity) {
                return $purchase;
            }
        }
    }
    public function determineQuantitySold($company, $product, $purchase, $row, $numberUnrecorded)
    {
        $numberSold = Sale::where('company_id', $company->id)->where('purchase_id', $purchase->id)->sum('quantity');
        $numberUnsold = $purchase->quantity - $numberSold;
        if ($numberUnrecorded < $numberUnsold)
        {
            return $numberUnrecorded;
        }
        else
        {
            return $numberUnsold;
        }
    }
    public function determineAmountSold($company, $product, $purchase, $row, $numberUnrecorded)
    {
        $numberSold = Sale::where('company_id', $company->id)->where('purchase_id', $purchase->id)->sum('quantity');
        $numberUnsold = $purchase->quantity - $numberSold;
        $amountSold = Sale::where('company_id', $company->id)->where('purchase_id', $purchase->id)->sum('amount');
        $amountUnsold = $purchase->amount - $amountSold;
        if ($numberUnrecorded < $numberUnsold)
        {
            $costOfSales = round($amountUnsold / $numberUnsold * $numberUnrecorded, 2);
            return $costOfSales;
        }
        else
        {
            return $amountUnsold;
        }
    }
}
