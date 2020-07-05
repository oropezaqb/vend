<?php

namespace App\Http\Controllers;

use App\JournalEntry;
use Illuminate\Http\Request;
use App\Document;
use App\Account;
use App\SubsidiaryLedger;
use App\ReportLineItem;
use Illuminate\Support\Facades\Validator;
use JavaScript;
use App\Posting;
use App\Http\Requests\StoreJournalEntry;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */

class JournalEntryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('company');
        $this->middleware('web');
    }
    public function index()
    {
        $company = \Auth::user()->currentCompany->company;
        if (empty(request('explanation'))) {
            $journalEntries = JournalEntry::where('company_id', $company->id)->latest()->get();
        } else {
            $journalEntries = JournalEntry::where('company_id', $company->id)
                ->where('explanation', 'like', '%' . request('explanation') . '%')->get();
        }
        if (\Route::currentRouteName() === 'journal_entries.index') {
            \Request::flash();
        }
        return view('journal_entries.index', compact('journalEntries'));
    }
    public function show(JournalEntry $journalEntry)
    {
        $company = \Auth::user()->currentCompany->company;
        $documents = Document::where('company_id', $company->id)->latest()->get();
        $accounts = Account::where('company_id', $company->id)->latest()->get();
        $subsidiaryLedgers = SubsidiaryLedger::where('company_id', $company->id)->latest()->get();
        $reportLineItems = ReportLineItem::where('company_id', $company->id)->latest()->get();
        return view(
            'journal_entries.show',
            compact('journalEntry', 'documents', 'accounts', 'subsidiaryLedgers', 'reportLineItems')
        );
    }
    public function create()
    {
        $company = \Auth::user()->currentCompany->company;
        $documents = Document::where('company_id', $company->id)->latest()->get();
        $accounts = Account::where('company_id', $company->id)->latest()->get();
        $subsidiaryLedgers = SubsidiaryLedger::where('company_id', $company->id)->latest()->get();
        $reportLineItems = ReportLineItem::where('company_id', $company->id)->latest()->get();
        return view(
            'journal_entries.create',
            compact('documents', 'accounts', 'subsidiaryLedgers', 'reportLineItems')
        );
    }
    public function store(StoreJournalEntry $request)
    {
        $company = \Auth::user()->currentCompany->company;
        $journalEntry = new JournalEntry([
            'company_id' => $company->id,
            'date' => request('date'),
            'document_type_id' => request('document_type_id'),
            'document_number' => request('document_number'),
            'explanation' => request('explanation')
        ]);
        $journalEntry->save();
        if (!is_null(request("postings.'account_id'"))) {
            $count = count(request("postings.'account_id'"));
            for ($row = 0; $row < $count; $row++) {
                $debit = request("postings.'debit'.".$row) - request("postings.'credit'.".$row);
                $posting = new Posting([
                    'account_id' => request("postings.'account_id'.".$row),
                    'debit' => $debit,
                    'subsidiary_ledger_id' => request("postings.'subsidiary_ledger_id'.".$row),
                    'report_line_item_id' => request("postings.'report_line_item_id'.".$row)
                ]);
                $posting->save();
                $journalEntry->post($posting);
            }
        }
        return redirect(route('journal_entries.index'));
    }
    public function edit(JournalEntry $journalEntry)
    {
        $company = \Auth::user()->currentCompany->company;
        $documents = Document::where('company_id', $company->id)->latest()->get();
        $accounts = Account::where('company_id', $company->id)->latest()->get();
        $subsidiaryLedgers = SubsidiaryLedger::where('company_id', $company->id)->latest()->get();
        $reportLineItems = ReportLineItem::where('company_id', $company->id)->latest()->get();
        return view(
            'journal_entries.edit',
            compact('journalEntry', 'documents', 'accounts', 'subsidiaryLedgers', 'reportLineItems')
        );
    }
    public function update(StoreJournalEntry $request, JournalEntry $journalEntry)
    {
        $company = \Auth::user()->currentCompany->company;
        $journalEntry->update([
            'company_id' => $company->id,
            'date' => request('date'),
            'document_type_id' => request('document_type_id'),
            'document_number' => request('document_number'),
            'explanation' => request('explanation')
        ]);
        $journalEntry->save();
        foreach ($journalEntry->postings as $posting) {
            $posting->delete();
        }
        if (!is_null(request("postings.'account_id'"))) {
            $count = count(request("postings.'account_id'"));
            for ($row = 0; $row < $count; $row++) {
                $debit = request("postings.'debit'.".$row) - request("postings.'credit'.".$row);
                $posting = new Posting([
                    'account_id' => request("postings.'account_id'.".$row),
                    'debit' => $debit,
                    'subsidiary_ledger_id' => request("postings.'subsidiary_ledger_id'.".$row),
                    'report_line_item_id' => request("postings.'report_line_item_id'.".$row)
                ]);
                $posting->save();
                $journalEntry->post($posting);
            }
        }
        return redirect(route('journal_entries.edit', [$journalEntry]))
            ->with('status', 'Journal entry updated!');
    }
    public function destroy(JournalEntry $journalEntry)
    {
        $journalEntry->delete();
        return redirect(route('journal_entries.index'));
    }
}
