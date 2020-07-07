<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDO;
use App\Ability;
use Illuminate\Support\Facades\Validator;
use App\EPMADD\DbAccess;
use App\EPMADD\Report;
use App\Query;
use Illuminate\Support\Facades\Storage;
use DateTime;
use Dompdf\Dompdf;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.ShortVariableName)
     */

class ReportController extends Controller
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
        $queries = Query::where('company_id', $company->id)->latest()->get();
        return view('reports.index', compact('queries'));
    }
    public function pdf(Query $query)
    {
        if (stripos($query->query, 'file ') === 0) {
            return redirect(route('queries.index'))->with('status', 'Cannot run file reports here.');
        } else {
            $db = new DbAccess();
            $stmt = $db->query($query->query);
            $r = new Report();
            $url = $r->pdf($query->title, $stmt);
            return view('reports.pdf', compact('url'));
        }
    }
    public function csv(Query $query)
    {
        if (stripos($query->query, 'file ') === 0) {
            return redirect(route('queries.index'))->with('status', 'Cannot run file reports here.');
        } else {
            $db = new DbAccess();
            $stmt = $db->query($query->query);
            $r = new Report();
            $url = $r->csv($stmt);
            return view('reports.csv', compact('url'));
        }
    }
    public function screen(Query $query)
    {
        if (stripos($query->query, 'file ') === 0) {
            return redirect(route('reports.index'))->with('status', 'Cannot run file reports here.');
        } else {
            $db = new DbAccess();
            //$query->query = $query->query . ' WHERE company_id=' . auth()->user()->currentCompany->company->id;
            $stmt = $db->query($query->query);
            $ncols = $stmt->columnCount();
            $headings = array();
            for ($i = 0; $i < $ncols; $i++) {
                $meta = $stmt->getColumnMeta($i);
                $headings[] = $meta['name'];
            }
            return view('reports.screen', compact('query', 'stmt', 'headings'));
        }
    }
    public function trialBalance()
    {
        return view('reports.trial_balance');
    }
    public function run(Request $request)
    {
        $query = new Query();
        $query->title = "Trial Balance";
        $date = DateTime::createFromFormat('Y-m-d', "$request->date");
        $query->date = 'As of ' . date_format($date, 'M d, Y');
        $db = new DbAccess();
        $stmt = $db->query("SELECT accounts.title, SUM(debit) debit FROM journal_entries
            RIGHT JOIN journal_entry_posting ON journal_entries.id = journal_entry_posting.journal_entry_id
            RIGHT JOIN postings ON journal_entry_posting.posting_id = postings.id
            RIGHT JOIN accounts ON postings.account_id = accounts.id
            WHERE accounts.company_id=" . auth()->user()->currentCompany->company->id . "
            AND journal_entries.date<=". "'" . request('date'). "'" ." GROUP BY accounts.title;");
        $headings = array('Account Title', 'Debit', 'Credit');
        return view('reports.trial_balance.screen', compact('query', 'stmt', 'headings'));
    }
    public function create()
    {
        $abilities = Ability::latest()->get();
        return view('queries.create', compact('abilities'));
    }
    public function show(ReportLineItem $reportLineItem)
    {
        return view('report_line_items.show', compact('reportLineItem'));
    }
    public function store(Request $request)
    {
        $messages = [
            'ability_id.exists' => 'The selected ability is invalid. Please choose among the recommended items.',
        ];

        $validator = Validator::make($request->all(), [
            'title' => ['required'],
            'category' => ['required'],
            'query' => ['required'],
            'ability_id' => ['required', 'exists:App\Ability,id'],
        ], $messages);

        $validator->after(function ($validator) {
            if (stripos(request('query'), 'select ') !== 0 && stripos(request('query'), 'file ') !== 0) {
                $validator->errors()->add('query', 'Only select or file queries are allowed.');
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $company = \Auth::user()->currentCompany->company;
        $query = new Query([
            'company_id' => $company->id,
            'title' => request('title'),
            'category' => request('category'),
            'query' => request('query'),
            'ability_id' => request('ability_id'),
        ]);
        $query->save();
        return redirect(route('queries.index'));
    }
    public function edit(ReportLineItem $reportLineItem)
    {
        if (\Route::currentRouteName() === 'report_line_items.edit') {
            \Request::flash();
        }
        return view('report_line_items.edit', compact('reportLineItem'));
    }
    public function update(ReportLineItem $reportLineItem)
    {
        $this->validateReportLineItem();
        $reportLineItem->update([
            'report' => request('report'),
            'section' => request('section'),
            'line_item' => request('line_item')
        ]);
        return redirect($reportLineItem->path());
    }
    public function destroy(ReportLineItem $reportLineItem)
    {
        $reportLineItem->delete();
        return redirect(route('report_line_items.index'));
    }
}
