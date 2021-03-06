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
use App\LineItem;
use App\ReportLineItem;

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
    public function financialPosition()
    {
        return view('reports.financial_position');
    }
    public function changesInEquity()
    {
        return view('reports.changes_in_equity');
    }
    public function cashFlows()
    {
        return view('reports.cash_flows');
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
    public function runFinancialPosition(Request $request)
    {
        $company = \Auth::user()->currentCompany->company;
        $query = new Query();
        $query->title = "Statement of Financial Position";
        $date = DateTime::createFromFormat('Y-m-d', "$request->date");
        $query->date = 'As of ' . date_format($date, 'M d, Y');
        $amounts = array();
        $currentAssets = \DB::table('journal_entries')
            ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
            ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
            ->leftJoin('line_items', 'accounts.line_item_id', '=', 'line_items.id')
            ->select('line_items.id', \DB::raw('SUM(debit) as debit'))
            ->where('journal_entries.company_id', $company->id)
            ->where('journal_entries.date', '<=', $date)
            ->where('accounts.type', '110 - Cash and Cash Equivalents')
            ->orWhere('accounts.type', '120 - Non-Cash Current Asset')
            ->groupBy('line_items.id')
            ->get();
        $amounts['current_assets'] = 0;
        foreach($currentAssets as $currentAsset)
        {
            $amounts['current_assets'] += $currentAsset->debit;
            $currentAsset->debit = $this->myformat($currentAsset->debit);
        }
        $noncurrentAssets = \DB::table('journal_entries')
            ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
            ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
            ->leftJoin('line_items', 'accounts.line_item_id', '=', 'line_items.id')
            ->select('line_items.id', \DB::raw('SUM(debit) as debit'))
            ->where('journal_entries.company_id', $company->id)
            ->where('journal_entries.date', '<=', $date)
            ->where('accounts.type', '150 - Non-Current Asset')
            ->groupBy('line_items.id')
            ->get();
        $amounts['noncurrent_assets'] = 0;
        foreach($noncurrentAssets as $noncurrentAsset)
        {
            $amounts['noncurrent_assets'] += $noncurrentAsset->debit;
            $noncurrentAsset->debit = $this->myformat($noncurrentAsset->debit);
        }
        $currentLiabilities = \DB::table('journal_entries')
            ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
            ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
            ->leftJoin('line_items', 'accounts.line_item_id', '=', 'line_items.id')
            ->select('line_items.id', \DB::raw('SUM(debit) as debit'))
            ->where('journal_entries.company_id', $company->id)
            ->where('journal_entries.date', '<=', $date)
            ->where('accounts.type', '210 - Current Liabilities')
            ->groupBy('line_items.id')
            ->get();
        $amounts['current_liabilities'] = 0;
        foreach($currentLiabilities as $currentLiability)
        {
            $currentLiability->debit *= -1;
            $amounts['current_liabilities'] += $currentLiability->debit;
            $currentLiability->debit = $this->myformat($currentLiability->debit);
        }
        $noncurrentLiabilities = \DB::table('journal_entries')
            ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
            ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
            ->leftJoin('line_items', 'accounts.line_item_id', '=', 'line_items.id')
            ->select('line_items.id', \DB::raw('SUM(debit) as debit'))
            ->where('journal_entries.company_id', $company->id)
            ->where('journal_entries.date', '<=', $date)
            ->where('accounts.type', '250 - Non-Current Liabilities')
            ->groupBy('line_items.id')
            ->get();
        $amounts['noncurrent_liabilities'] = 0;
        foreach($noncurrentLiabilities as $noncurrentLiability)
        {
            $noncurrentLiability->debit *= -1;
            $amounts['noncurrent_liabilities'] += $noncurrentLiability->debit;
            $noncurrentLiability->debit = $this->myformat($noncurrentLiability->debit);
        }
        $equities = \DB::table('journal_entries')
            ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
            ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
            ->leftJoin('line_items', 'accounts.line_item_id', '=', 'line_items.id')
            ->select('line_items.id', \DB::raw('SUM(debit) as debit'))
            ->where('journal_entries.company_id', $company->id)
            ->where('journal_entries.date', '<=', $date)
            ->where('accounts.type', '310 - Capital')
            ->orWhere('accounts.type', '320 - Share Premium')
            ->orWhere('accounts.type', '340 - Other Comprehensive Income')
            ->groupBy('line_items.id')
            ->get();
        $amounts['total_equity'] = 0;
        foreach($equities as $equity)
        {
            $equity->debit *= -1;
            $amounts['total_equity'] += $equity->debit;
            $equity->debit = $this->myformat($equity->debit);
        }
        $appropriatedREs = \DB::table('journal_entries')
            ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
            ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
            ->leftJoin('line_items', 'accounts.line_item_id', '=', 'line_items.id')
            ->select('line_items.id', \DB::raw('SUM(debit) as debit'))
            ->where('journal_entries.company_id', $company->id)
            ->where('journal_entries.date', '<=', $date)
            ->where('accounts.type', '330 - Retained Earnings')
            ->where('line_items.name', 'Appropriated retained earnings')
            ->groupBy('line_items.id')
            ->get();
        foreach($appropriatedREs as $appropriatedRE)
        {
            $appropriatedRE->debit *= -1;
            $amounts['total_equity'] += $appropriatedRE->debit;
            $appropriatedRE->debit = $this->myformat($appropriatedRE->debit);
        }
        $amounts['retained_earnings'] = \DB::table('journal_entries')
            ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
            ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
            ->leftJoin('line_items', 'accounts.line_item_id', '=', 'line_items.id')
            ->select('line_items.id', \DB::raw('SUM(debit) as debit'))
            ->where('journal_entries.company_id', $company->id)
            ->where('journal_entries.date', '<=', $date)
            ->where('accounts.type', '330 - Retained Earnings')
            ->whereNotIn('line_items.name', ['Appropriated retained earnings'])
            ->sum('postings.debit');
        $amounts['retained_earnings'] += \DB::table('journal_entries')
            ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
            ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
            ->leftJoin('line_items', 'accounts.line_item_id', '=', 'line_items.id')
            ->select('line_items.id', \DB::raw('SUM(debit) as debit'))
            ->where('journal_entries.company_id', $company->id)
            ->where('journal_entries.date', '<=', $date)
            ->where('accounts.type', '350 - Drawing')
            ->orWhere('accounts.type', '390 - Income Summary')
            ->orWhere('accounts.type', '410 - Revenue')
            ->orWhere('accounts.type', '420 - Other Income')
            ->orWhere('accounts.type', '510 - Cost of Goods Sold')
            ->orWhere('accounts.type', '520 - Operating Expense')
            ->orWhere('accounts.type', '590 - Income Tax Expense')
            ->sum('postings.debit');
        $amounts['total_assets'] = $amounts['current_assets'] + $amounts['noncurrent_assets'];
        $amounts['total_liabilities'] = $amounts['current_liabilities'] + $amounts['noncurrent_liabilities'];
        $amounts['retained_earnings'] *= -1;
        $amounts['total_equity'] += $amounts['retained_earnings'];
        $amounts['liabilities_equity'] = $amounts['total_liabilities'] + $amounts['total_equity'];
        if($amounts['total_assets'] != $amounts['liabilities_equity']){
            return back()->with('status', 'Error found. Please contact your system developer.')->withInput();
        }
        $amounts['current_assets'] = $this->myformat($amounts['current_assets']);
        $amounts['noncurrent_assets'] = $this->myformat($amounts['noncurrent_assets']);
        $amounts['total_assets'] = $this->myformat($amounts['total_assets']);
        $amounts['current_liabilities'] = $this->myformat($amounts['current_liabilities']);
        $amounts['noncurrent_liabilities'] = $this->myformat($amounts['noncurrent_liabilities']);
        $amounts['total_liabilities'] = $this->myformat($amounts['total_liabilities']);
        $amounts['retained_earnings'] = $this->myformat($amounts['retained_earnings']);
        $amounts['total_equity'] = $this->myformat($amounts['total_equity']);
        $amounts['liabilities_equity'] = $this->myformat($amounts['liabilities_equity']);        
        return view('reports.financial_position.screen', compact('query', 'amounts', 'currentAssets', 'noncurrentAssets', 'currentLiabilities', 'noncurrentLiabilities', 'equities', 'appropriatedREs'));
    }
    public function runChangesInEquity(Request $request)
    {
        $company = \Auth::user()->currentCompany->company;
        $query = new Query();
        $query->title = "Statement of Changes in Equity";
        $begDate = DateTime::createFromFormat('Y-m-d', "$request->beg_date");
        $endDate = DateTime::createFromFormat('Y-m-d', "$request->end_date");
        $query->date = 'For the Period ' . date_format($begDate, 'M d, Y') . ' - ' . date_format($endDate, 'M d, Y');
        $amounts = array();
        $reportLineItems = \DB::table('journal_entries')
            ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
            ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
            ->leftJoin('line_items', 'accounts.line_item_id', '=', 'line_items.id')
            ->leftJoin('report_line_items', 'postings.report_line_item_id', '=', 'report_line_items.id')
            ->where([
                ['journal_entries.company_id', $company->id],
                ['journal_entries.date', '>=', $begDate],
                ['journal_entries.date', '<=', $endDate],
                ['accounts.type', '310 - Capital'],
                ['report_line_items.line_item', '!=', 'Other comprehensive income']
            ])
            ->orWhere(function($query) use ($company, $begDate, $endDate) {
                $query->where('journal_entries.company_id', $company->id)
                      ->where('journal_entries.date', '>=', $begDate)
                      ->where('journal_entries.date', '<=', $endDate)
                      ->where('accounts.type', '320 - Share Premium')
                      ->where('report_line_items.line_item', '!=', 'Other comprehensive income');
            })
            ->orWhere(function($query) use ($company, $begDate, $endDate) {
                $query->where('journal_entries.company_id', $company->id)
                      ->where('journal_entries.date', '>=', $begDate)
                      ->where('journal_entries.date', '<=', $endDate)
                      ->where('accounts.type', '340 - Other Comprehensive Income')
                      ->where('report_line_items.line_item', '!=', 'Other comprehensive income');
            })
            ->orWhere(function($query) use ($company, $begDate, $endDate) {
                $query->where('journal_entries.company_id', $company->id)
                      ->where('journal_entries.date', '>=', $begDate)
                      ->where('journal_entries.date', '<=', $endDate)
                      ->where('accounts.type', '330 - Retained Earnings')
                      ->where('report_line_items.line_item', '!=', 'Other comprehensive income');
            })
            ->select('report_line_items.id')
            ->groupBy('report_line_items.id')
            ->get();
        $equities = \DB::table('journal_entries')
            ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
            ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
            ->leftJoin('line_items', 'accounts.line_item_id', '=', 'line_items.id')
            ->where([
                ['journal_entries.company_id', $company->id],
                ['journal_entries.date', '<=', $endDate],
                ['accounts.type', '310 - Capital']
            ])
            ->orWhere(function($query) use ($company, $endDate) {
                $query->where('journal_entries.company_id', $company->id)
                      ->where('journal_entries.date', '<=', $endDate)
                      ->where('accounts.type', '320 - Share Premium');
            })
            ->orWhere(function($query) use ($company, $endDate) {
                $query->where('journal_entries.company_id', $company->id)
                      ->where('journal_entries.date', '<=', $endDate)
                      ->where('accounts.type', '340 - Other Comprehensive Income');
            })
            ->select('line_items.id', \DB::raw('SUM(debit) as debit'))
            ->groupBy('line_items.id')
            ->get();
        $appropriatedREs = \DB::table('journal_entries')
            ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
            ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
            ->leftJoin('line_items', 'accounts.line_item_id', '=', 'line_items.id')
            ->select('line_items.id', \DB::raw('SUM(debit) as debit'))
            ->where('journal_entries.company_id', $company->id)
            ->where('journal_entries.date', '<=', $endDate)
            ->where('accounts.type', '330 - Retained Earnings')
            ->where('line_items.name', 'Appropriated retained earnings')
            ->groupBy('line_items.id')
            ->get();
        $amounts['beg_total_equity'] = 0;
        foreach($equities as $equity)
        {
            $lineItem = LineItem::find($equity->id);
            $begAmount = \DB::table('journal_entries')
                ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
                ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
                ->leftJoin('line_items', 'accounts.line_item_id', '=', 'line_items.id')
                ->where([
                    ['journal_entries.company_id', $company->id],
                    ['journal_entries.date', '<', $begDate],
                    ['accounts.type', '310 - Capital'],
                    ['line_items.id', $lineItem->id]
                ])
                ->orWhere(function($query) use ($company, $begDate, $lineItem) {
                    $query->where('journal_entries.company_id', $company->id)
                          ->where('journal_entries.date', '<', $begDate)
                          ->where('accounts.type', '320 - Share Premium')
                          ->where('line_items.id', $lineItem->id);
                })
                ->orWhere(function($query) use ($company, $begDate, $lineItem) {
                    $query->where('journal_entries.company_id', $company->id)
                          ->where('journal_entries.date', '<', $begDate)
                          ->where('accounts.type', '340 - Other Comprehensive Income')
                          ->where('line_items.id', $lineItem->id);
                })
                ->sum('debit');
            $begAmount *= -1;
            $amounts['beg_total_equity'] += $begAmount;
            $amounts['beg'][] = $this->myformat($begAmount);
        }
        $amounts['total_OCI'] = 0;
        $amounts['total_TCI'] = 0;
        $tCICtr = 0;
        foreach($equities as $equity)
        {
            $lineItem = LineItem::find($equity->id);
            $OCIAmount = \DB::table('journal_entries')
                ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
                ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
                ->leftJoin('line_items', 'accounts.line_item_id', '=', 'line_items.id')
                ->leftJoin('report_line_items', 'postings.report_line_item_id', '=', 'report_line_items.id')
                ->where([
                    ['journal_entries.company_id', $company->id],
                    ['journal_entries.date', '>=', $begDate],
                    ['journal_entries.date', '<=', $endDate],
                    ['accounts.type', '310 - Capital'],
                    ['line_items.id', $lineItem->id],
                    ['report_line_items.line_item', 'Other comprehensive income']
                ])
                ->orWhere(function($query) use ($company, $begDate, $endDate, $lineItem) {
                    $query->where('journal_entries.company_id', $company->id)
                          ->where('journal_entries.date', '>=', $begDate)
                          ->where('journal_entries.date', '<=', $endDate)
                          ->where('accounts.type', '320 - Share Premium')
                          ->where('line_items.id', $lineItem->id)
                          ->where('report_line_items.line_item', 'Other comprehensive income');
                })
                ->orWhere(function($query) use ($company, $begDate, $endDate, $lineItem) {
                    $query->where('journal_entries.company_id', $company->id)
                          ->where('journal_entries.date', '>=', $begDate)
                          ->where('journal_entries.date', '<=', $endDate)
                          ->where('accounts.type', '340 - Other Comprehensive Income')
                          ->where('line_items.id', $lineItem->id)
                          ->where('report_line_items.line_item', 'Other comprehensive income');
                })
                ->sum('debit');
            $OCIAmount *= -1;
            $amounts['total_OCI'] += $OCIAmount;
            $amounts['TCI'][$tCICtr] = $OCIAmount;
            $amounts['total_TCI'] += $OCIAmount;
            $amounts['OCI'][] = $this->myformat($OCIAmount);
            $amounts['TCI'][$tCICtr] = $this->myformat($amounts['TCI'][$tCICtr]);
            $tCICtr += 1;
        }
        $tCICtr = 0;
        foreach($appropriatedREs as $appropriatedRE)
        {
            $lineItem = LineItem::find($appropriatedRE->id);
            $begAmount = \DB::table('journal_entries')
                ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
                ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
                ->leftJoin('line_items', 'accounts.line_item_id', '=', 'line_items.id')
                ->where([
                    ['journal_entries.company_id', $company->id],
                    ['journal_entries.date', '<', $begDate],
                    ['accounts.type', '330 - Retained Earnings'],
                    ['line_items.id', $lineItem->id]
                ])
                ->sum('debit');
            $begAmount *= -1;
            $amounts['beg_total_equity'] += $begAmount;
            $amounts['beg'][] = $this->myformat($begAmount);
        }
        foreach($appropriatedREs as $appropriatedRE)
        {
            $lineItem = LineItem::find($appropriatedRE->id);
            $OCIAmount = 0;
            $OCIAmount *= -1;
            $amounts['total_OCI'] += $OCIAmount;
            $amounts['OCI'][] = $this->myformat($OCIAmount);
        }
        $amounts['total_OCI'] = $this->myformat($amounts['total_OCI']);
        $amounts['beg_retained_earnings'] = \DB::table('journal_entries')
            ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
            ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
            ->leftJoin('line_items', 'accounts.line_item_id', '=', 'line_items.id')
            ->select('line_items.id', \DB::raw('SUM(debit) as debit'))
            ->where(function($query) use ($company, $begDate, $lineItem) {
                    $query->where('journal_entries.company_id', $company->id)
                          ->where('journal_entries.date', '<', $begDate)
                          ->where('accounts.type', '330 - Retained Earnings')
                          ->whereNotIn('line_items.name', ['Appropriated retained earnings']);
                })
            ->sum('postings.debit');
        $amounts['beg_retained_earnings'] += \DB::table('journal_entries')
            ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
            ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
            ->leftJoin('line_items', 'accounts.line_item_id', '=', 'line_items.id')
            ->select('line_items.id', \DB::raw('SUM(debit) as debit'))
            ->where([
                    ['journal_entries.company_id', $company->id],
                    ['journal_entries.date', '<', $begDate],
                    ['accounts.type', '350 - Drawing'],
                ])
            ->orWhere(function($query) use ($company, $begDate, $lineItem) {
                    $query->where('journal_entries.company_id', $company->id)
                          ->where('journal_entries.date', '<', $begDate)
                          ->where('accounts.type', '390 - Income Summary');
                })
            ->orWhere(function($query) use ($company, $begDate, $lineItem) {
                    $query->where('journal_entries.company_id', $company->id)
                          ->where('journal_entries.date', '<', $begDate)
                          ->where('accounts.type', '410 - Revenue');
                })
            ->orWhere(function($query) use ($company, $begDate, $lineItem) {
                    $query->where('journal_entries.company_id', $company->id)
                          ->where('journal_entries.date', '<', $begDate)
                          ->where('accounts.type', '420 - Other Income');
                })
            ->orWhere(function($query) use ($company, $begDate, $lineItem) {
                    $query->where('journal_entries.company_id', $company->id)
                          ->where('journal_entries.date', '<', $begDate)
                          ->where('accounts.type', '510 - Cost of Goods Sold');
                })
            ->orWhere(function($query) use ($company, $begDate, $lineItem) {
                    $query->where('journal_entries.company_id', $company->id)
                          ->where('journal_entries.date', '<', $begDate)
                          ->where('accounts.type', '520 - Operating Expense');
                })
            ->orWhere(function($query) use ($company, $begDate, $lineItem) {
                    $query->where('journal_entries.company_id', $company->id)
                          ->where('journal_entries.date', '<', $begDate)
                          ->where('accounts.type', '590 - Income Tax Expense');
                })
            ->sum('postings.debit');
        $amounts['beg_retained_earnings'] *= -1;
        $amounts['beg_total_equity'] += $amounts['beg_retained_earnings'];
        $amounts['beg_retained_earnings'] = $this->myformat($amounts['beg_retained_earnings']);
        $amounts['beg_total_equity'] = $this->myformat($amounts['beg_total_equity']);
        foreach($reportLineItems as $reportLineItem)
        {
            $reportLineItem = ReportLineItem::find($reportLineItem->id);
            $amounts['line_item_total'][$reportLineItem->id] = 0;
            foreach($equities as $equity)
            {
                $lineItem = LineItem::find($equity->id);
                $amount = \DB::table('journal_entries')
                    ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
                    ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
                    ->leftJoin('line_items', 'accounts.line_item_id', '=', 'line_items.id')
                    ->leftJoin('report_line_items', 'postings.report_line_item_id', '=', 'report_line_items.id')
                    ->where([
                        ['journal_entries.company_id', $company->id],
                        ['journal_entries.date', '>=', $begDate],
                        ['journal_entries.date', '<=', $endDate],
                        ['accounts.type', '310 - Capital'],
                        ['line_items.id', $lineItem->id],
                        ['report_line_items.line_item', $reportLineItem->line_item]
                    ])
                    ->orWhere(function($query) use ($company, $begDate, $endDate, $lineItem, $reportLineItem) {
                        $query->where('journal_entries.company_id', $company->id)
                              ->where('journal_entries.date', '>=', $begDate)
                              ->where('journal_entries.date', '<=', $endDate)
                              ->where('accounts.type', '320 - Share Premium')
                              ->where('line_items.id', $lineItem->id)
                              ->where('report_line_items.line_item', $reportLineItem->line_item);
                    })
                    ->orWhere(function($query) use ($company, $begDate, $endDate, $lineItem, $reportLineItem) {
                        $query->where('journal_entries.company_id', $company->id)
                              ->where('journal_entries.date', '>=', $begDate)
                              ->where('journal_entries.date', '<=', $endDate)
                              ->where('accounts.type', '340 - Other Comprehensive Income')
                              ->where('line_items.id', $lineItem->id)
                              ->where('report_line_items.line_item', $reportLineItem->line_item);
                    })
                    ->sum('debit');
                $amount *= -1;
                $amounts[$reportLineItem->id][$equity->id] = $amount;
                $amounts['line_item_total'][$reportLineItem->id] += $amount;
                $amounts[$reportLineItem->id][$equity->id] = $this->myformat($amounts[$reportLineItem->id][$equity->id]);            
            }
            foreach($appropriatedREs as $appropriatedRE)
            {
                $lineItem = LineItem::find($appropriatedRE->id);
                $amount = \DB::table('journal_entries')
                    ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
                    ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
                    ->leftJoin('line_items', 'accounts.line_item_id', '=', 'line_items.id')
                    ->leftJoin('report_line_items', 'postings.report_line_item_id', '=', 'report_line_items.id')
                    ->where([
                        ['journal_entries.company_id', $company->id],
                        ['journal_entries.date', '>=', $begDate],
                        ['journal_entries.date', '<=', $endDate],
                        ['accounts.type', '330 - Retained Earnings'],
                        ['line_items.id', $lineItem->id],
                        ['report_line_items.line_item', $reportLineItem->line_item]
                    ])
                    ->sum('debit');
                $amount *= -1;
                $amounts[$reportLineItem->id][$appropriatedRE->id] = $amount;
                $amounts['line_item_total'][$reportLineItem->id] += $amount;
                $amounts[$reportLineItem->id][$appropriatedRE->id] = $this->myformat($amounts[$reportLineItem->id][$appropriatedRE->id]);
            }
            $rEAmount = \DB::table('journal_entries')
                ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
                ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
                ->leftJoin('line_items', 'accounts.line_item_id', '=', 'line_items.id')
                ->leftJoin('report_line_items', 'postings.report_line_item_id', '=', 'report_line_items.id')
                ->where(function($query) use ($company, $begDate, $endDate, $lineItem, $reportLineItem) {
                    $query->where('journal_entries.company_id', $company->id)
                          ->where('journal_entries.date', '>=', $begDate)
                          ->where('journal_entries.date', '<=', $endDate)
                          ->where('accounts.type', '330 - Retained Earnings')
                          ->whereNotIn('line_items.name', ['Appropriated retained earnings'])
                          ->where('report_line_items.line_item', $reportLineItem->line_item);
                })
                ->sum('postings.debit');
            $rEAmount *= -1;
            $amounts['retained_earnings'][$reportLineItem->id] = $rEAmount;
            $amounts['line_item_total'][$reportLineItem->id] += $rEAmount;
            $amounts['retained_earnings'][$reportLineItem->id] = $this->myformat($amounts['retained_earnings'][$reportLineItem->id]);
            $amounts['line_item_total'][$reportLineItem->id] = $this->myformat($amounts['line_item_total'][$reportLineItem->id]);
        }
        $amounts['end_total_equity'] = 0;
        foreach($equities as $equity)
        {
            $equity->debit *= -1;
            $amounts['end_total_equity'] += $equity->debit;
            $equity->debit = $this->myformat($equity->debit);
        }
        foreach($appropriatedREs as $appropriatedRE)
        {
            $appropriatedRE->debit *= -1;
            $amounts['end_total_equity'] += $appropriatedRE->debit;
            $appropriatedRE->debit = $this->myformat($appropriatedRE->debit);
        }
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
        $amounts['total_TCI'] += $amounts['net_income'];
        $amounts['total_TCI'] = $this->myformat($amounts['total_TCI']);
        $amounts['other_comprehensive_income'] = \DB::table('journal_entries')
            ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
            ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
            ->where('journal_entries.date', '>=', $begDate)
            ->where('journal_entries.date', '<=', $endDate)
            ->where('accounts.type', '340 - Other Comprehensive Income')
            ->sum('debit');;
        $amounts['other_comprehensive_income'] *= -1;
        $amounts['total_comprehensive_income'] = $this->myformat($amounts['net_income'] + $amounts['other_comprehensive_income']);
        $amounts['net_income'] = $this->myformat($amounts['net_income']);
        $amounts['other_comprehensive_income'] = $this->myformat($amounts['other_comprehensive_income']);

        $amounts['end_retained_earnings'] = \DB::table('journal_entries')
            ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
            ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
            ->leftJoin('line_items', 'accounts.line_item_id', '=', 'line_items.id')
            ->select('line_items.id', \DB::raw('SUM(debit) as debit'))
            ->where(function($query) use ($company, $endDate, $lineItem) {
                    $query->where('journal_entries.company_id', $company->id)
                          ->where('journal_entries.date', '<=', $endDate)
                          ->where('accounts.type', '330 - Retained Earnings')
                          ->whereNotIn('line_items.name', ['Appropriated retained earnings']);
                })
            ->sum('postings.debit');
        $amounts['end_retained_earnings'] += \DB::table('journal_entries')
            ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
            ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
            ->leftJoin('line_items', 'accounts.line_item_id', '=', 'line_items.id')
            ->select('line_items.id', \DB::raw('SUM(debit) as debit'))
            ->where([
                    ['journal_entries.company_id', $company->id],
                    ['journal_entries.date', '<=', $endDate],
                    ['accounts.type', '350 - Drawing'],
                ])
            ->orWhere(function($query) use ($company, $endDate, $lineItem) {
                    $query->where('journal_entries.company_id', $company->id)
                          ->where('journal_entries.date', '<=', $endDate)
                          ->where('accounts.type', '390 - Income Summary');
                })
            ->orWhere(function($query) use ($company, $endDate, $lineItem) {
                    $query->where('journal_entries.company_id', $company->id)
                          ->where('journal_entries.date', '<=', $endDate)
                          ->where('accounts.type', '410 - Revenue');
                })
            ->orWhere(function($query) use ($company, $endDate, $lineItem) {
                    $query->where('journal_entries.company_id', $company->id)
                          ->where('journal_entries.date', '<=', $endDate)
                          ->where('accounts.type', '420 - Other Income');
                })
            ->orWhere(function($query) use ($company, $endDate, $lineItem) {
                    $query->where('journal_entries.company_id', $company->id)
                          ->where('journal_entries.date', '<=', $endDate)
                          ->where('accounts.type', '510 - Cost of Goods Sold');
                })
            ->orWhere(function($query) use ($company, $endDate, $lineItem) {
                    $query->where('journal_entries.company_id', $company->id)
                          ->where('journal_entries.date', '<=', $endDate)
                          ->where('accounts.type', '520 - Operating Expense');
                })
            ->orWhere(function($query) use ($company, $endDate, $lineItem) {
                    $query->where('journal_entries.company_id', $company->id)
                          ->where('journal_entries.date', '<=', $endDate)
                          ->where('accounts.type', '590 - Income Tax Expense');
                })
            ->sum('postings.debit');
        $amounts['end_retained_earnings'] *= -1;
        $amounts['end_total_equity'] += $amounts['end_retained_earnings'];
        $amounts['end_retained_earnings'] = $this->myformat($amounts['end_retained_earnings']);
        $amounts['end_total_equity'] = $this->myformat($amounts['end_total_equity']);
        return view('reports.changes_in_equity.screen', compact('query', 'amounts', 'equities', 'appropriatedREs', 'begDate', 'endDate', 'reportLineItems'));
    }
    public function runCashFlows(Request $request)
    {
        $company = \Auth::user()->currentCompany->company;
        $query = new Query();
        $query->title = "Statement of Cash Flows";
        $begDate = DateTime::createFromFormat('Y-m-d', "$request->beg_date");
        $endDate = DateTime::createFromFormat('Y-m-d', "$request->end_date");
        $query->date = 'For the Period ' . date_format($begDate, 'M d, Y') . ' - ' . date_format($endDate, 'M d, Y');
        $amounts = array();
        $operatingCashFlows = \DB::table('journal_entries')
            ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
            ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
            ->leftJoin('line_items', 'accounts.line_item_id', '=', 'line_items.id')
            ->leftJoin('report_line_items', 'postings.report_line_item_id', '=', 'report_line_items.id')
            ->where([
                ['journal_entries.company_id', $company->id],
                ['journal_entries.date', '>=', $begDate],
                ['journal_entries.date', '<=', $endDate],
                ['accounts.type', '110 - Cash and Cash Equivalents'],
                ['report_line_items.report', 'Statement of Cash Flows'],
                ['report_line_items.section', 'Operating Activities']
            ])
            ->select('report_line_items.id', \DB::raw('SUM(debit) as debit'))
            ->groupBy('report_line_items.id')
            ->get();
        $amounts['net_increase'] = 0;
        $amounts['operating_total'] = 0;
        foreach($operatingCashFlows as $operatingCashFlow)
        {
            $amounts['operating_total'] += $operatingCashFlow->debit;
            $operatingCashFlow->debit = $this->myformat($operatingCashFlow->debit);
        }
        $amounts['net_increase'] += $amounts['operating_total'];
        $amounts['operating_total'] = $this->myformat($amounts['operating_total']);
        $investingCashFlows = \DB::table('journal_entries')
            ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
            ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
            ->leftJoin('line_items', 'accounts.line_item_id', '=', 'line_items.id')
            ->leftJoin('report_line_items', 'postings.report_line_item_id', '=', 'report_line_items.id')
            ->where([
                ['journal_entries.company_id', $company->id],
                ['journal_entries.date', '>=', $begDate],
                ['journal_entries.date', '<=', $endDate],
                ['accounts.type', '110 - Cash and Cash Equivalents'],
                ['report_line_items.report', 'Statement of Cash Flows'],
                ['report_line_items.section', 'Investing Activities']
            ])
            ->select('report_line_items.id', \DB::raw('SUM(debit) as debit'))
            ->groupBy('report_line_items.id')
            ->get();
        $amounts['investing_total'] = 0;
        foreach($investingCashFlows as $investingCashFlow)
        {
            $amounts['investing_total'] += $investingCashFlow->debit;
            $investingCashFlow->debit = $this->myformat($investingCashFlow->debit);
        }
        $amounts['net_increase'] += $amounts['investing_total'];
        $amounts['investing_total'] = $this->myformat($amounts['investing_total']);
        $financingCashFlows = \DB::table('journal_entries')
            ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
            ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
            ->leftJoin('line_items', 'accounts.line_item_id', '=', 'line_items.id')
            ->leftJoin('report_line_items', 'postings.report_line_item_id', '=', 'report_line_items.id')
            ->where([
                ['journal_entries.company_id', $company->id],
                ['journal_entries.date', '>=', $begDate],
                ['journal_entries.date', '<=', $endDate],
                ['accounts.type', '110 - Cash and Cash Equivalents'],
                ['report_line_items.report', 'Statement of Cash Flows'],
                ['report_line_items.section', 'Financing Activities']
            ])
            ->select('report_line_items.id', \DB::raw('SUM(debit) as debit'))
            ->groupBy('report_line_items.id')
            ->get();
        $amounts['financing_total'] = 0;
        foreach($financingCashFlows as $financingCashFlow)
        {
            $amounts['financing_total'] += $financingCashFlow->debit;
            $financingCashFlow->debit = $this->myformat($financingCashFlow->debit);
        }
        $amounts['net_increase'] += $amounts['financing_total'];
        $amounts['financing_total'] = $this->myformat($amounts['financing_total']);
        $amounts['beg'] = \DB::table('journal_entries')
            ->rightJoin('postings', 'journal_entries.id', '=', 'postings.journal_entry_id')
            ->leftJoin('accounts', 'postings.account_id', '=', 'accounts.id')
            ->leftJoin('line_items', 'accounts.line_item_id', '=', 'line_items.id')
            ->leftJoin('report_line_items', 'postings.report_line_item_id', '=', 'report_line_items.id')
            ->where([
                ['journal_entries.company_id', $company->id],
                ['journal_entries.date', '<', $begDate],
                ['accounts.type', '110 - Cash and Cash Equivalents']
            ])
            ->sum('debit');
        $amounts['end'] = $amounts['net_increase'] + $amounts['beg'];
        $amounts['net_increase'] = $this->myformat($amounts['net_increase']);
        $amounts['beg'] = $this->myformat($amounts['beg']);
        $amounts['end'] = $this->myformat($amounts['end']);
        return view('reports.cash_flows.screen', compact('query', 'amounts', 'operatingCashFlows', 'investingCashFlows', 'financingCashFlows'));
    }
}
