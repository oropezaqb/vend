<?php

namespace App\Http\Controllers;

use App\Bill;
use Illuminate\Http\Request;
use App\Supplier;
use App\Account;
use App\Product;
use Illuminate\Support\Facades\Validator;
use JavaScript;
use App\BillCategoryLine;
use App\BillItemLine;
use App\Http\Requests\StoreBill;
use App\Purchase;
use App\Jobs\CreateBill;
use App\Jobs\CreateInvoice;
use App\Document;
use App\JournalEntry;
use App\Posting;
use App\SubsidiaryLedger;
use DB;
use App\Transaction;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UndefinedVariable)
     * @SuppressWarnings(PHPMD.ShortVariableName)
     */

class BillController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('company');
        $this->middleware('web');
        $this->middleware('accountsPayable');
        $this->middleware('inputVat');
    }
    public function index()
    {
        $company = \Auth::user()->currentCompany->company;
        if (empty(request('supplier_name'))) {
            $bills = Bill::where('company_id', $company->id)->latest()->get();
        } else {
            $supplier = Supplier::where('name', request('supplier_name'))->firstOrFail();
            $bills = Bill::where('company_id', $company->id)
                ->where('supplier_id', $supplier->id)->latest()->get();
        }
        if (\Route::currentRouteName() === 'bills.index') {
            \Request::flash();
        }
        return view('bills.index', compact('bills'));
    }
    public function show(Bill $bill)
    {
        $company = \Auth::user()->currentCompany->company;
        $suppliers = Supplier::where('company_id', $company->id)->latest()->get();
        $accounts = Account::where('company_id', $company->id)->latest()->get();
        $products = Product::where('company_id', $company->id)->latest()->get();
        return view(
            'bills.show',
            compact('bill', 'suppliers', 'accounts', 'products')
        );
    }
    public function create()
    {
        $company = \Auth::user()->currentCompany->company;
        $suppliers = Supplier::where('company_id', $company->id)->latest()->get();
        $accounts = Account::where('company_id', $company->id)->latest()->get();
        $products = Product::where('company_id', $company->id)->latest()->get();
        return view(
            'bills.create',
            compact('suppliers', 'accounts', 'products')
        );
    }
    public function store(StoreBill $request)
    {
        try {
            \DB::transaction(function () use ($request) {
                $company = \Auth::user()->currentCompany->company;
                $bill = new Bill([
                    'company_id' => $company->id,
                    'supplier_id' => request('supplier_id'),
                    'bill_date' => request('bill_date'),
                    'due_date' => request('due_date'),
                    'bill_number' => request('bill_number'),
                ]);
                $bill->save();
                $createBill = new CreateBill();
                $createBill->updateLines($bill);
                $createBill->recordJournalEntry($bill);
                $createBill->recordPurchases($bill);
                $salesForUpdate = DB::table('transactions')->where('company_id', $company->id)->where('type', 'sale')
                    ->where('date', '>=', request('bill_date'))->orderBy('date', 'asc')->get();
                $createInvoice = new CreateInvoice();
                $createInvoice->updateSales($salesForUpdate);
            });
            return redirect(route('bills.index'));
        } catch (\Exception $e) {
            return back()->with('status', $this->translateError($e))->withInput();
        }
    }
    public function translateError($e)
    {
        switch ($e->getCode()) {
            case '23000':
                if (preg_match(
                    "/for key '(.*)'/",
                    $e->getMessage(),
                    $m
                )) {
                    $indexes = array(
                      'my_unique_ref' =>
                    array ('Bill is already recorded.', 'bill_number'));
                    if (isset($indexes[$m[1]])) {
                        $this->err_flds = array($indexes[$m[1]][1] => 1);
                        return $indexes[$m[1]][0];
                    }
                }
                break;
        }
        return $e->getMessage();
    }
    public function edit(Bill $bill)
    {
        $company = \Auth::user()->currentCompany->company;
        $suppliers = Supplier::where('company_id', $company->id)->latest()->get();
        $accounts = Account::where('company_id', $company->id)->latest()->get();
        $products = Product::where('company_id', $company->id)->latest()->get();
        return view(
            'bills.edit',
            compact('bill', 'suppliers', 'accounts', 'products')
        );
    }
    public function update(StoreBill $request, Bill $bill)
    {
        try {
            \DB::transaction(function () use ($request, $bill) {
                $company = \Auth::user()->currentCompany->company;
                $oldDate = $bill->bill_date;
                $newDate = request('bill_date');
                $bill->update([
                    'company_id' => $company->id,
                    'supplier_id' => request('supplier_id'),
                    'bill_date' => request('bill_date'),
                    'due_date' => request('due_date'),
                    'bill_number' => request('bill_number'),
                ]);
                $bill->save();
                $changeDate = $newDate;
                if ($oldDate < $newDate) {
                    $changeDate = $oldDate;
                }
                $salesForUpdate = DB::table('transactions')->where('company_id', $company->id)->where('type', 'sale')
                    ->where('date', '>=', $changeDate)->orderBy('date', 'asc')->get();
                $bill->journalEntry()->delete();
                $createBill = new CreateBill();
                $createBill->deleteBillDetails($bill);
                $createBill->updateLines($bill);
                $createBill->recordJournalEntry($bill);
                $createBill->recordPurchases($bill);
                $createInvoice = new CreateInvoice();
                $createInvoice->updateSales($salesForUpdate);
            });
            return redirect(route('bills.edit', [$bill]))
                ->with('status', 'Bill updated!');
        } catch (\Exception $e) {
            return back()->with('status', $this->translateError($e))->withInput();
        }
    }
    public function destroy(Bill $bill)
    {
        try {
            \DB::transaction(function () use ($bill) {
                $company = \Auth::user()->currentCompany->company;
                $billDate = $bill->bill_date;
                $bill->delete();
                $salesForUpdate = DB::table('transactions')->where('company_id', $company->id)->where('type', 'sale')
                    ->where('date', '>=', $billDate)->orderBy('date', 'asc')->get();
                $createInvoice = new CreateInvoice();
                $createInvoice->updateSales($salesForUpdate);
            });
            return redirect(route('bills.index'));
        } catch (\Exception $e) {
            return back()->with('status', $this->translateError($e))->withInput();
        }
    }
}
