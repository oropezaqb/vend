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
    public function updateLines($creditNote)
    {
        if (!is_null(request("item_lines.'product_id'"))) {
            $count = count(request("item_lines.'product_id'"));
            for ($row = 0; $row < $count; $row++) {
                $outputTax = 0;
                if (!is_null(request("item_lines.'output_tax'.".$row))) {
                    $outputTax = request("item_lines.'output_tax'.".$row);
                }
                if (!is_null(request("item_lines.'amount'.".$row)) &&
                    is_numeric(request("item_lines.'amount'.".$row))) {
                    if (request("item_lines.'amount'.".$row) > 0) {
                        $itemLine = new CreditNoteLine([
                            'credit_note_id' => $creditNote->id,
                            'invoice_id' => request('invoice_id'),
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
        }
    }
}
