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
use App\Jobs\RecordSalesReturn;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */

class UpdateSales
{
    public function updateSales($salesForUpdate)
    {
        foreach ($salesForUpdate as $saleForUpdate) {
            $transactions = Transaction::all();
            $transaction = $transactions->find($saleForUpdate->id);
            if ($transaction->type == 'sale') {
                $this->deleteSales($transaction);
            }
            if ($transaction->type == 'sales_return') {
                $this->deleteSales($transaction);
            }
        }
        foreach ($salesForUpdate as $saleForUpdate) {
            $transactions = Transaction::all();
            $transaction = $transactions->find($saleForUpdate->id);
            if ($transaction->type == 'sale') {
                $invoice = $transaction->transactable;
                $input = array();
                $row = 0;
                $input['customer_id'] = $invoice->customer_id;
                $input['date'] = $invoice->date;
                $input['invoice_number'] = $invoice->invoice_number;
                foreach ($invoice->itemLines as $itemLine) {
                    $input['item_lines']["'product_id'"][$row] = $itemLine->product_id;
                    $input['item_lines']["'description'"][$row] = $itemLine->description;
                    $input['item_lines']["'quantity'"][$row] = $itemLine->quantity;
                    $input['item_lines']["'amount'"][$row] = $itemLine->amount;
                    $input['item_lines']["'output_tax'"][$row] = $itemLine->output_tax;
                    $row += 1;
                }
                $createInvoice = new CreateInvoice();
                $createInvoice->recordSales($invoice, $input);
                $createInvoice->recordJournalEntry($invoice, $input);
            }
            if ($transaction->type == 'sales_return') {
                $creditNote = $transaction->transactable;
                $input = array();
                $row = 0;
                $input['customer_id'] = $creditNote->invoice->customer_id;
                $input['date'] = $creditNote->date;
                $input['number'] = $creditNote->number;
                foreach ($creditNote->lines as $itemLine) {
                    $input['item_lines']["'product_id'"][$row] = $itemLine->product_id;
                    $input['item_lines']["'description'"][$row] = $itemLine->description;
                    $input['item_lines']["'quantity'"][$row] = $itemLine->quantity;
                    $input['item_lines']["'amount'"][$row] = $itemLine->amount;
                    $input['item_lines']["'output_tax'"][$row] = $itemLine->output_tax;
                    $row += 1;
                }
                $recordSalesReturn = new RecordSalesReturn();
                $recordSalesReturn->record($creditNote, $input);
                $createInvoice = new CreateCreditNote();
                $createInvoice->recordJournalEntry($creditNote, $input);
                $createInvoice->recordPurchases($creditNote);
            }
        }
    }
    public function deleteSales($transaction)
    {
        $invoice = $transaction->transactable;
        if (is_object($invoice->journalEntry)) {
            foreach ($invoice->journalEntry->postings as $posting) {
                $posting->delete();
            }
            $invoice->journalEntry->delete();
        }
        if (is_object($invoice->sales)) {
            $sales = $invoice->sales;
            foreach ($sales as $sale) {
                $sale->delete();
            }
        }
    }
}
