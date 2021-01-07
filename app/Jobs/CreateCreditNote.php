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
use App\CreditNoteLine;
use App\InvoiceItemLine;

class CreateCreditNote
{
    public function determineAmounts($invoice_id, $invoice_line_id, $quantity)
    {
        $invoiceLine = Product::find($invoice_line_id);
        $quantitySold = InvoiceItemLine::where('invoice_id', $invoice_id)->where('product_id', $invoiceLine->id)->sum('quantity');
        $amountSold = $invoiceLine->amount;
        $taxSold = $invoiceLine->output_tax;
        $quantityReturned = CreditNoteLine::where('invoice_id', $invoice_id)->where('product_id', $invoiceLine->id)->sum('quantity');
        $amountReturned = CreditNoteLine::where('invoice_id', $invoice_id)->where('product_id', $invoiceLine->id)->sum('amount');
        $taxReturned = CreditNoteLine::where('invoice_id', $invoice_id)->where('product_id', $invoiceLine->id)->sum('output_tax');
        $quantityUnreturned = $quantitySold - $quantityReturned;
        $amountUnreturned = $amountSold - $amountReturned;
        $taxUnreturned = $taxSold - $taxReturned;
        $amounts = array();
        if($quantity > 0 && $quantity < $quantityUnreturned)
        {
            $amounts['amount'] = round(($amountUnreturned / $quantityUnreturned) * $quantity, 2);
            $amounts['tax'] = round(($taxUnreturned / $quantityUnreturned) * $quantity, 2);
        }
        if($quantity == $quantityUnreturned)
        {
            $amounts['amount'] = $amountUnreturned;
            $amounts['tax'] = $taxUnreturned;
        }
        return $amounts;
    }
}
