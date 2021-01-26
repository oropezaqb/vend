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
    public function determineAmounts($invoiceId, $invoiceLineId, $quantity)
    {
        $invoiceLine = Product::find($invoiceLineId);
        $quantitySold = InvoiceItemLine::where('invoice_id', $invoiceId)
            ->where('product_id', $invoiceLine->id)->sum('quantity');
        $amountSold = InvoiceItemLine::where('invoice_id', $invoiceId)
            ->where('product_id', $invoiceLine->id)->sum('amount');
        $taxSold = InvoiceItemLine::where('invoice_id', $invoiceId)
            ->where('product_id', $invoiceLine->id)->sum('output_tax');
        $quantityReturned = CreditNoteLine::where('invoice_id', $invoiceId)
            ->where('product_id', $invoiceLine->id)->sum('quantity');
        $amountReturned = CreditNoteLine::where('invoice_id', $invoiceId)
            ->where('product_id', $invoiceLine->id)->sum('amount');
        $taxReturned = CreditNoteLine::where('invoice_id', $invoiceId)
            ->where('product_id', $invoiceLine->id)->sum('output_tax');
        $quantityUnreturned = $quantitySold - $quantityReturned;
        $amountUnreturned = $amountSold - $amountReturned;
        $taxUnreturned = $taxSold - $taxReturned;
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
}
