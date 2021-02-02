<?php

namespace App\Jobs;

use App\Customer;
use App\Account;
use App\Product;
use App\Document;
use App\SubsidiaryLedger;
use App\Transaction;
use App\CreditNoteLine;
use App\InvoiceItemLine;
use App\SalesReturn;
use App\Invoice;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */

class RecordSalesReturn
{
    public function record($creditNote, $input)
    {
        $count = count($input['item_lines']["'product_id'"], 1);
        if ($count > 0) {
            for ($row = 0; $row < $count; $row++) {
                $product = Product::find($input['item_lines']["'product_id'"][$row]);
                if ($product->track_quantity) {
                    $numberRecorded = 0;
                    do {
                        $company = \Auth::user()->currentCompany->company;
                        $sale = $this->determineSaleReturned($company, $product);
                        if (is_object($sale)) {
                            $numberUnrecorded = $input['item_lines']["'quantity'"][$row] - $numberRecorded;
                            $quantity = $this->determineQtyReturned($company, $sale, $numberUnrecorded);
                            $amount = $this->determineAmtReturned($company, $sale, $numberUnrecorded);
                            $salesReturn = new SalesReturn([
                                'company_id' => $company->id,
                                'sale_id' => $sale->id,
                                'date' => $input['date'],
                                'product_id' => $product->id,
                                'quantity' => $quantity,
                                'amount' => $amount
                            ]);
                            $creditNote->salesReturns()->save($salesReturn);
                            $numberRecorded += $quantity;
                        } else {
                            break;
                        }
                    } while ($numberRecorded < $input['item_lines']["'quantity'"][$row]);
                }
            }
        }
    }
    public function determineSaleReturned($company, $product)
    {
        $invoice = Invoice::find(request('invoice_id'));
        $sales = $invoice->sales;
        foreach ($sales as $sale) {
            if ($sale->product_id == $product->id) {
                $numberReturned = SalesReturn::where('company_id', $company->id)
                    ->where('sale_id', $sale->id)->sum('quantity');
                if ($numberReturned < $sale->quantity) {
                    return $sale;
                }
            }
        }
    }
    public function determineQtyReturned($company, $sale, $numberUnrecorded)
    {
        $numberReturned = SalesReturn::where('company_id', $company->id)->where('sale_id', $sale->id)->sum('quantity');
        $numberUnreturned = $sale->quantity - $numberReturned;
        if ($numberUnrecorded < $numberUnreturned) {
            return $numberUnrecorded;
        } else {
            return $numberUnreturned;
        }
    }
    public function determineAmtReturned($company, $sale, $numberUnrecorded)
    {
        $numberReturned = SalesReturn::where('company_id', $company->id)->where('sale_id', $sale->id)->sum('quantity');
        $numberUnreturned = $sale->quantity - $numberReturned;
        $amountReturned = SalesReturn::where('company_id', $company->id)->where('sale_id', $sale->id)->sum('amount');
        $amountUnreturned = $sale->amount - $amountReturned;
        if ($numberUnrecorded < $numberUnreturned) {
            $costOfReturns = round($amountUnreturned / $numberUnreturned * $numberUnrecorded, 2);
            return $costOfReturns;
        } else {
            return $amountUnreturned;
        }
    }
}
