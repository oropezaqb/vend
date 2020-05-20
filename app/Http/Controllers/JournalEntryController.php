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

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
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
        $company = \Auth::user()->current_company->company;
        if (empty(request('explanation')))
        {
            $journalEntries = JournalEntry::where('company_id', $company->id)->latest()->get();
        }
        else
        {
            $journalEntries = JournalEntry::where('company_id', $company->id)->where('explanation', 'like', '%' . request('explanation') . '%')->get();
        }
        if (\Route::currentRouteName() === 'journal_entries.index')
        {
            \Request::flash();
        }
        return view('journal_entries.index', compact('journalEntries'));
    }
    public function show(JournalEntry $journalEntry)
    {
        $company = \Auth::user()->current_company->company;
        $documents = Document::where('company_id', $company->id)->latest()->get();
        $accounts = Account::where('company_id', $company->id)->latest()->get();
        $subsidiaryLedgers = SubsidiaryLedger::where('company_id', $company->id)->latest()->get();
        $reportLineItems = ReportLineItem::where('company_id', $company->id)->latest()->get();
        return view('journal_entries.show', compact('journalEntry', 'documents', 'accounts', 'subsidiaryLedgers', 'reportLineItems'));
    }
    public function create()
    {
        $company = \Auth::user()->current_company->company;
        $documents = Document::where('company_id', $company->id)->latest()->get();
        $accounts = Account::where('company_id', $company->id)->latest()->get();
        $subsidiaryLedgers = SubsidiaryLedger::where('company_id', $company->id)->latest()->get();
        $reportLineItems = ReportLineItem::where('company_id', $company->id)->latest()->get();
        return view('journal_entries.create', compact('documents', 'accounts', 'subsidiaryLedgers', 'reportLineItems'));
    }
    public function store(Request $request)
    {
        $messages = [
            'document_type_id.required' => 'The document type field is required.', 
            'document_type_id.exists' => 'The selected document type is invalid. Please choose among the recommended items.',
            'document_number.min' => 'The document number must be a positive number.',
            "postings.'account_id'.min" => 'There must be at least two account titles.',
            "postings.'account_id'.*.exists" => 'Some account titles are invalid. Please choose among the recommended items.',
            "postings.'debit'.*.min" => 'Debit amounts must be positive',
            "postings.'credit'.*.min" => 'Credit amounts must be positive',
            "postings.'subsidiary_ledger_id'.*.exists" => 'Some subsidiary ledgers are invalid. Please choose among the recommended items.',
            "postings.'report_line_item_id'.*.exists" => 'Some report line items are invalid. Please choose among the recommended items.',
        ];

        $validator = Validator::make($request->all(), [
            'date' => ['required', 'date'],
            'document_type_id' => ['required', 'exists:App\Document,id'],
            'document_number' => ['required', 'numeric', 'min:1'],
            'explanation' => ['required'],
            "postings.'account_id'" => ['sometimes', 'min:2'],
            "postings.'account_id'.*" => [
                'sometimes', 
                'required', 
                'exists:App\Account,id'
            ],
            "postings.'debit'.*" => ['sometimes', 'numeric', 'min:0.01', 'nullable'],
            "postings.'credit'.*" => ['sometimes', 'numeric', 'min:0.01', 'nullable'],
            "postings.'subsidiary_ledger_id'.*" => ['sometimes', 'exists:App\SubsidiaryLedger,id', 'nullable'],
            "postings.'report_line_item_id'.*" => ['sometimes', 'exists:App\ReportLineItem,id', 'nullable']
        ], $messages);

        $validator->after(function ($validator) {
            if (!is_null(request("postings.'account_id'"))) {
                for ($x = 0; $x < count(request("postings.'account_id'")); $x++) {
                    if ((is_null(request("postings.'debit'.".$x)) && is_null(request("postings.'credit'.".$x))) || (!is_null(request("postings.'debit'.".$x)) && !is_null(request("postings.'credit'.".$x)))) {
                        $validator->errors()->add('postings', 'Line ' . ($x + 1) . ' must have either a debit or a credit (not both).');
                    }
                    if (Account::all()->contains(request("postings.'account_id'.".$x)))
                    {
                        if ((Account::find(request("postings.'account_id'.".$x))->subsidiary_ledger == TRUE) && (is_null(request("postings.'subsidiary_ledger_id'.".$x)))) {
                            $validator->errors()->add('postings', 'Line ' . ($x + 1) . ' requires a subsidiary ledger.');
                        }
                        $account = Account::find(request("postings.'account_id'.".$x));
                        if (($account->type == '110 - Cash and Cash Equivalents' || 
                             $account->type == '310 - Capital' ||
                             $account->type == '320 - Share Premium' ||
                             $account->type == '330 - Retained Earnings' ||
                             $account->type == '340 - Other Comprehensive Income')
                             && (is_null(request("postings.'report_line_item_id'.".$x)))) 
                        {
                            $validator->errors()->add('postings', 'Line ' . ($x + 1) . ' requires a report line item.');
                        }
                    }
                }
                if (array_sum(request("postings.'debit'")) != array_sum(request("postings.'credit'"))) {
                    $validator->errors()->add('postings', 'Total debits must equal total credits');
                }
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $company = \Auth::user()->current_company->company;
        $journalEntry = new JournalEntry([
            'company_id' => $company->id,
            'date' => request('date'),
            'document_type_id' => request('document_type_id'),
            'document_number' => request('document_number'),
            'explanation' => request('explanation')
        ]);
        $journalEntry->save();
        if (!is_null(request("postings.'account_id'"))) {
            for ($row = 0; $row < count(request("postings.'account_id'")); $row++)
            {
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
        $company = \Auth::user()->current_company->company;
        $documents = Document::where('company_id', $company->id)->latest()->get();
        $accounts = Account::where('company_id', $company->id)->latest()->get();
        $subsidiaryLedgers = SubsidiaryLedger::where('company_id', $company->id)->latest()->get();
        $reportLineItems = ReportLineItem::where('company_id', $company->id)->latest()->get();
        return view('journal_entries.edit', compact('journalEntry', 'documents', 'accounts', 'subsidiaryLedgers', 'reportLineItems'));
    }
    public function update(Request $request, JournalEntry $journalEntry)
    {
        $messages = [
            'document_type_id.required' => 'The document type field is required.', 
            'document_type_id.exists' => 'The selected document type is invalid. Please choose among the recommended items.',
            'document_number.min' => 'The document number must be a positive number.',
            "postings.'account_id'.min" => 'There must be at least two account titles.',
            "postings.'account_id'.*.exists" => 'Some account titles are invalid. Please choose among the recommended items.',
            "postings.'debit'.*.min" => 'Debit amounts must be positive',
            "postings.'credit'.*.min" => 'Credit amounts must be positive',
            "postings.'subsidiary_ledger_id'.*.exists" => 'Some subsidiary ledgers are invalid. Please choose among the recommended items.',
            "postings.'report_line_item_id'.*.exists" => 'Some report line items are invalid. Please choose among the recommended items.',
        ];

        $validator = Validator::make($request->all(), [
            'date' => ['required', 'date'],
            'document_type_id' => ['required', 'exists:App\Document,id'],
            'document_number' => ['required', 'numeric', 'min:1'],
            'explanation' => ['required'],
            "postings.'account_id'" => ['sometimes', 'min:2'],
            "postings.'account_id'.*" => [
                'sometimes', 
                'required', 
                'exists:App\Account,id'
            ],
            "postings.'debit'.*" => ['sometimes', 'numeric', 'min:0.01', 'nullable'],
            "postings.'credit'.*" => ['sometimes', 'numeric', 'min:0.01', 'nullable'],
            "postings.'subsidiary_ledger_id'.*" => ['sometimes', 'exists:App\SubsidiaryLedger,id', 'nullable'],
            "postings.'report_line_item_id'.*" => ['sometimes', 'exists:App\ReportLineItem,id', 'nullable']
        ], $messages);

        $validator->after(function ($validator) {
            if (!is_null(request("postings.'account_id'"))) {
                for ($x = 0; $x < count(request("postings.'account_id'")); $x++) {
                    if ((is_null(request("postings.'debit'.".$x)) && is_null(request("postings.'credit'.".$x))) || (!is_null(request("postings.'debit'.".$x)) && !is_null(request("postings.'credit'.".$x)))) {
                        $validator->errors()->add('postings', 'Line ' . ($x + 1) . ' must have either a debit or a credit (not both).');
                    }
                    if (Account::all()->contains(request("postings.'account_id'.".$x)))
                    {
                        if ((Account::find(request("postings.'account_id'.".$x))->subsidiary_ledger == TRUE) && (is_null(request("postings.'subsidiary_ledger_id'.".$x)))) {
                            $validator->errors()->add('postings', 'Line ' . ($x + 1) . ' requires a subsidiary ledger.');
                        }
                        $account = Account::find(request("postings.'account_id'.".$x));
                        if (($account->type == '110 - Cash and Cash Equivalents' || 
                             $account->type == '310 - Capital' ||
                             $account->type == '320 - Share Premium' ||
                             $account->type == '330 - Retained Earnings' ||
                             $account->type == '340 - Other Comprehensive Income')
                             && (is_null(request("postings.'report_line_item_id'.".$x)))) 
                        {
                            $validator->errors()->add('postings', 'Line ' . ($x + 1) . ' requires a report line item.');
                        }
                    }
                }
                if (array_sum(request("postings.'debit'")) != array_sum(request("postings.'credit'"))) {
                    $validator->errors()->add('postings', 'Total debits must equal total credits');
                }
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $company = \Auth::user()->current_company->company;

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
            for ($row = 0; $row < count(request("postings.'account_id'")); $row++)
            {
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
        return redirect(route('journal_entries.edit', [$journalEntry]))->with('status', 'Journal entry updated!');
    }
    public function validateJournalEntry($request)
    {

    }
    public function destroy(JournalEntry $journalEntry)
    {
        $journalEntry->delete();
        return redirect(route('journal_entries.index'));
    }
}
