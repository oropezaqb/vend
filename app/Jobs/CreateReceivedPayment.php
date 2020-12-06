<?php

namespace App\Jobs;

use App\Customer;
use App\Account;
use App\Product;
use App\Purchase;
use App\Document;
use App\JournalEntry;
use App\Posting;
use App\SubsidiaryLedger;
use App\ReportLineItem;

class CreateReceivedPayment
{
    public function recordJournalEntry($receivedPayment)
    {
        $company = \Auth::user()->currentCompany->company;
        $document = Document::firstOrCreate(['name' => 'Received Payment', 'company_id' => $company->id]);
        $receivableAccount = Account::firstOrCreate(['title' => 'Accounts Receivable', 'company_id' => $company->id]);
        $customer = Customer::all()->find(request('customer_id'));
        $receivableSubsidiary = SubsidiaryLedger::firstOrCreate(['name' => $customer->name,
            'company_id' => $company->id]);
        $reportLineItem = ReportLineItem::firstOrCreate(['report' => 'Statement of Cash Flows',
            'section' => 'Cash flow from operations',
            'line_item' => 'Cash received from customers', 'company_id' => $company->id]);
        $journalEntry = new JournalEntry([
            'company_id' => $company->id,
            'date' => request('date'),
            'document_type_id' => $document->id,
            'document_number' => request('number'),
            'explanation' => 'To record receipt of payment from customer.'
        ]);
        $receivedPayment->journalEntry()->save($journalEntry);
        $paymentAmount = 0;
        $lines = $receivedPayment->lines;
        if (!is_null($lines)) {
            $count = count($lines);
            for ($row = 0; $row < $count; $row++) {
                $paymentAmount = $lines[$row]['amount'];
            }
        }
        $posting = new Posting([
            'company_id' => $company->id,
            'journal_entry_id' => $journalEntry->id,
            'account_id' => request('account_id'),
            'debit' => $paymentAmount,
            'report_line_item_id' => $reportLineItem->id
        ]);
        $posting->save();
        $posting2 = new Posting([
            'company_id' => $company->id,
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $receivableAccount->id,
            'debit' => -$paymentAmount,
            'subsidiary_ledger_id' => $receivableSubsidiary->id
        ]);
        $posting2->save();
    }
    public function deleteReceivedPayment($receivedPayment)
    {
        foreach ($receivedPayment->lines as $line) {
            $line->delete();
        }
    }
}
