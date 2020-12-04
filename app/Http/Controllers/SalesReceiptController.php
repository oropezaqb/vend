<?php

namespace App\Http\Controllers;

use App\SalesReceipt;
use Illuminate\Http\Request;
use App\Customer;
use App\Account;
use App\Product;
use Illuminate\Support\Facades\Validator;
use JavaScript;
use App\SalesReceiptItemLine;
use App\Http\Requests\StoreSalesReceipt;
use App\Purchase;
use App\Sale;
use App\Jobs\CreateSalesReceipt;
use App\Transaction;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UndefinedVariable)
     * @SuppressWarnings(PHPMD.ShortVariableName)
     */

class SalesReceiptController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('company');
        $this->middleware('web');
        $this->middleware('outputVat');
    }
    public function index()
    {
        $company = \Auth::user()->currentCompany->company;
        if (empty(request('customer_name'))) {
            $salesReceipts = SalesReceipt::where('company_id', $company->id)->latest()->get();
        } else {
            $customer = Customer::where('name', request('customer_name'))->firstOrFail();
            $salesReceipts = SalesReceipt::where('company_id', $company->id)
                ->where('customer_id', $customer->id)->latest()->get();
        }
        if (\Route::currentRouteName() === 'sales_receipts.index') {
            \Request::flash();
        }
        return view('sales_receipts.index', compact('salesReceipts'));
    }
    public function show(SalesReceipt $salesReceipt)
    {
        $company = \Auth::user()->currentCompany->company;
        $customers = Customer::where('company_id', $company->id)->latest()->get();
        $accounts = Account::where('company_id', $company->id)->latest()->get();
        $products = Product::where('company_id', $company->id)->latest()->get();
        return view(
            'sales_receipts.show',
            compact('salesReceipt', 'customers', 'accounts', 'products')
        );
    }
    public function create()
    {
        $company = \Auth::user()->currentCompany->company;
        $customers = Customer::where('company_id', $company->id)->latest()->get();
        $accounts = Account::where('company_id', $company->id)->latest()->get();
        $products = Product::where('company_id', $company->id)->latest()->get();
        return view(
            'sales_receipts.create',
            compact('customers', 'accounts', 'products')
        );
    }
    public function store(StoreSalesReceipt $request)
    {
        try {
            \DB::transaction(function () use ($request) {
                $company = \Auth::user()->currentCompany->company;
                $salesReceipt = new SalesReceipt([
                    'company_id' => $company->id,
                    'customer_id' => request('customer_id'),
                    'date' => request('date'),
                    'number' => request('number'),
                    'account_id' => request('account_id'),
                ]);
                $salesReceipt->save();
                $createSalesReceipt = new CreateSalesReceipt();
                $createSalesReceipt->updateLines($salesReceipt);
                $createSalesReceipt->recordTransaction($salesReceipt);
                $salesForUpdate = \DB::table('transactions')->where('company_id', $company->id)
                    ->where('type', 'sale')
                    ->where('date', '>=', request('date'))->orderBy('date', 'asc')->get();
                $createSalesReceipt->updateSales($salesForUpdate);
            });
            return redirect(route('sales_receipts.index'));
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
                    array ('Sales receipt is already recorded.', 'number'));
                    if (isset($indexes[$m[1]])) {
                        $this->err_flds = array($indexes[$m[1]][1] => 1);
                        return $indexes[$m[1]][0];
                    }
                }
                break;
        }
        return $e->getMessage();
    }

    public function edit(SalesReceipt $salesReceipt)
    {
        $company = \Auth::user()->currentCompany->company;
        $customers = Customer::where('company_id', $company->id)->latest()->get();
        $accounts = Account::where('company_id', $company->id)->latest()->get();
        $products = Product::where('company_id', $company->id)->latest()->get();
        return view(
            'sales_receipts.edit',
            compact('salesReceipt', 'customers', 'accounts', 'products')
        );
    }
    public function update(StoreSalesReceipt $request, SalesReceipt $salesReceipt)
    {
        try {
            \DB::transaction(function () use ($request, $salesReceipt) {
                $company = \Auth::user()->currentCompany->company;
                $oldDate = $salesReceipt->date;
                $newDate = request('date');
                $salesReceipt->update([
                    'company_id' => $company->id,
                    'customer_id' => request('customer_id'),
                    'date' => request('date'),
                    'number' => request('number'),
                    'account_id' => request('account_id'),
                ]);
                $salesReceipt->save();
                $changeDate = $newDate;
                if ($oldDate < $newDate) {
                    $changeDate = $oldDate;
                }
                $salesForUpdate = \DB::table('transactions')->where('company_id', $company->id)
                    ->where('type', 'sale')->where('date', '>=', $changeDate)->orderBy('date', 'asc')->get();
                $salesReceipt->journalEntry()->delete();
                $createSalesReceipt = new CreateSalesReceipt();
                $createSalesReceipt->deleteSalesReceiptDetails($salesReceipt);
                $createSalesReceipt->updateLines($salesReceipt);
                $createSalesReceipt->updateSales($salesForUpdate);
            });
            return redirect(route('sales_receipts.edit', [$salesReceipt]))
                ->with('status', 'Sales receipt updated!');
        } catch (\Exception $e) {
            return back()->with('status', $this->translateError($e))->withInput();
        }
    }
    public function destroy(SalesReceipt $salesReceipt)
    {
        try {
            \DB::transaction(function () use ($salesReceipt) {
                $company = \Auth::user()->currentCompany->company;
                $salesReceiptDate = $salesReceipt->date;
                $salesReceipt->delete();
                $salesForUpdate = \DB::table('transactions')->where('company_id', $company->id)->where('type', 'sale')
                    ->where('date', '>=', $salesReceiptDate)->orderBy('date', 'asc')->get();
                $createSalesReceipt = new CreateSalesReceipt();
                $createSalesReceipt->updateSales($salesForUpdate);
            });
            return redirect(route('sales_receipts.index'));
        } catch (\Exception $e) {
            return back()->with('status', $this->translateError($e))->withInput();
        }
    }
}
