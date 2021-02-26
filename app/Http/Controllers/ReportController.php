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
use App\Posting;

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
    public function comprehensiveIncome()
    {
        return view('reports.comprehensive_income');
    }
    public function run(Request $request)
    {
        $query = new Query();
        $query->title = "Trial Balance";
        $date = DateTime::createFromFormat('Y-m-d', "$request->date");
        $query->date = 'As of ' . date_format($date, 'M d, Y');
        $db = new DbAccess();
        $stmt = $db->query("SELECT accounts.id, accounts.title, accounts.type AS theType, SUM(debit) debit
            FROM journal_entries
            RIGHT JOIN postings ON journal_entries.id = postings.journal_entry_id
            RIGHT JOIN accounts ON postings.account_id = accounts.id
            WHERE accounts.company_id=" . auth()->user()->currentCompany->company->id . "
            AND journal_entries.date<=". "'" . request('date'). "'" ."
            GROUP BY accounts.id
            ORDER BY theType ASC;");
        $headings = array('Account Title', 'Debit', 'Credit');
        return view('reports.trial_balance.screen', compact('query', 'stmt', 'headings'));
    }
    public function runComprehensiveIncome(Request $request)
    {
        $query = new Query();
        $query->title = "Statement of Comprehensive Income";
        $begDate = DateTime::createFromFormat('Y-m-d', "$request->beg_date");
        $endDate = DateTime::createFromFormat('Y-m-d', "$request->end_date");
        $query->date = date_format($begDate, 'M d, Y') . ' - ' . date_format($endDate, 'M d, Y');
        $amounts = array();
        $amounts['revenue'] = \DB::table('journal_entries')
            ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
            ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
            ->where('journal_entries.date', '>=', $begDate)
            ->where('journal_entries.date', '<=', $endDate)
            ->where('accounts.type', '410 - Revenue')
            ->sum('debit');
        $amounts['revenue'] *= -1;
        $amounts['other_income'] = \DB::table('journal_entries')
            ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
            ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
            ->where('journal_entries.date', '>=', $begDate)
            ->where('journal_entries.date', '<=', $endDate)
            ->where('accounts.type', '420 - Other Income')
            ->sum('debit');
        $amounts['other_income'] *= -1;
        $amounts['total_income'] = $amounts['revenue'] + $amounts['other_income'];
        $amounts['cost_of_goods_sold'] = \DB::table('journal_entries')
            ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
            ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
            ->where('journal_entries.date', '>=', $begDate)
            ->where('journal_entries.date', '<=', $endDate)
            ->where('accounts.type', '510 - Cost of Goods Sold')
            ->sum('debit');
        $amounts['gross_profit'] = $amounts['total_income'] - $amounts['cost_of_goods_sold'];
        $amounts['expenses'] = \DB::table('journal_entries')
            ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
            ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
            ->where('journal_entries.date', '>=', $begDate)
            ->where('journal_entries.date', '<=', $endDate)
            ->where('accounts.type', '520 - Operating Expense')
            ->sum('debit');
        $amounts['profit_before_tax'] = $amounts['gross_profit'] - $amounts['expenses'];
        $amounts['income_tax'] = \DB::table('journal_entries')
            ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
            ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
            ->where('journal_entries.date', '>=', $begDate)
            ->where('journal_entries.date', '<=', $endDate)
            ->where('accounts.type', '590 - Income Tax Expense')
            ->sum('debit');
        $amounts['net_income'] = $amounts['profit_before_tax'] - $amounts['income_tax'];
        $amounts['other_comprehensive_income'] = \DB::table('journal_entries')
            ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
            ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
            ->where('journal_entries.date', '>=', $begDate)
            ->where('journal_entries.date', '<=', $endDate)
            ->where('accounts.type', '340 - Other Comprehensive Income')
            ->sum('debit');;
        $amounts['other_comprehensive_income'] *= -1;
        $amounts['total_comprehensive_income'] = $this->myformat($amounts['net_income'] + $amounts['other_comprehensive_income']);
        $amounts['revenue'] = $this->myformat($amounts['revenue']);
        $amounts['other_income'] = $this->myformat($amounts['other_income']);
        $amounts['total_income'] = $this->myformat($amounts['total_income']);
        $amounts['cost_of_goods_sold'] = $this->myformat($amounts['cost_of_goods_sold']);
        $amounts['gross_profit'] = $this->myformat($amounts['gross_profit']);
        $amounts['expenses'] = $this->myformat($amounts['expenses']);
        $amounts['profit_before_tax'] = $this->myformat($amounts['profit_before_tax']);
        $amounts['income_tax'] = $this->myformat($amounts['income_tax']);
        $amounts['net_income'] = $this->myformat($amounts['net_income']);
        $amounts['other_comprehensive_income'] = $this->myformat($amounts['other_comprehensive_income']);
        return view('reports.comprehensive_income.screen', compact('query', 'amounts'));
    }
    function myformat($nr)
    {
        $nr = number_format($nr, 2);
        return $nr[0] == '-' ? "(" . substr($nr, 1) . ")" : $nr;
    }
}
