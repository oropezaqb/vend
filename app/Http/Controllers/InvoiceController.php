<?php

namespace App\Http\Controllers;

use App\Invoice;
use Illuminate\Http\Request;
use App\Customer;
use App\Account;
use App\Product;
use Illuminate\Support\Facades\Validator;
use JavaScript;
use App\InvoiceItemLine;
use App\Http\Requests\StoreInvoice;
use App\Purchase;
use App\Sale;
use App\Jobs\CreateInvoice;
use App\Transaction;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UndefinedVariable)
     * @SuppressWarnings(PHPMD.ShortVariableName)
     */

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('company');
        $this->middleware('web');
        $this->middleware('accountsReceivable');
        $this->middleware('outputVat');
    }
    public function index()
    {
        $company = \Auth::user()->currentCompany->company;
        if (empty(request('customer_name'))) {
            $invoices = Invoice::where('company_id', $company->id)->latest()->get();
        } else {
            $customer = Customer::where('name', request('customer_name'))->firstOrFail();
            $invoices = Invoice::where('company_id', $company->id)
                ->where('customer_id', $customer->id)->latest()->get();
        }
        if (\Route::currentRouteName() === 'invoices.index') {
            \Request::flash();
        }
        return view('invoices.index', compact('invoices'));
    }
    public function show(Invoice $invoice)
    {
        $company = \Auth::user()->currentCompany->company;
        $customers = Customer::where('company_id', $company->id)->latest()->get();
        $accounts = Account::where('company_id', $company->id)->latest()->get();
        $products = Product::where('company_id', $company->id)->latest()->get();
        return view(
            'invoices.show',
            compact('invoice', 'customers', 'accounts', 'products')
        );
    }
    public function create()
    {
        $company = \Auth::user()->currentCompany->company;
        $customers = Customer::where('company_id', $company->id)->latest()->get();
        $accounts = Account::where('company_id', $company->id)->latest()->get();
        $products = Product::where('company_id', $company->id)->latest()->get();
        return view(
            'invoices.create',
            compact('customers', 'accounts', 'products')
        );
    }
    public function store(StoreInvoice $request)
    {
        try {
            \DB::transaction(function () use ($request) {
                $company = \Auth::user()->currentCompany->company;
                $invoice = new Invoice([
                    'company_id' => $company->id,
                    'customer_id' => request('customer_id'),
                    'invoice_date' => request('invoice_date'),
                    'due_date' => request('due_date'),
                    'invoice_number' => request('invoice_number'),
                ]);
                $invoice->save();
                $createInvoice = new CreateInvoice();
                $createInvoice->updateLines($invoice);
                $createInvoice->recordTransaction($invoice);
                $salesForUpdate = \DB::table('transactions')->where('company_id', $company->id)->where('type', 'sale')
                    ->where('date', '>=', request('invoice_date'))->orderBy('date', 'asc')->get();
                $createInvoice->updateSales($salesForUpdate);
            });
            return redirect(route('invoices.index'));
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
                    array ('Invoice is already recorded.', 'invoice_number'));
                    if (isset($indexes[$m[1]])) {
                        $this->err_flds = array($indexes[$m[1]][1] => 1);
                        return $indexes[$m[1]][0];
                    }
                }
                break;
        }
        return $e->getMessage();
    }
    public function edit(Invoice $invoice)
    {
        $company = \Auth::user()->currentCompany->company;
        $customers = Customer::where('company_id', $company->id)->latest()->get();
        $products = Product::where('company_id', $company->id)->latest()->get();
        return view(
            'invoices.edit',
            compact('invoice', 'customers', 'products')
        );
    }
    public function update(StoreInvoice $request, Invoice $invoice)
    {
        try {
            \DB::transaction(function () use ($request, $invoice) {
                $company = \Auth::user()->currentCompany->company;
                $oldDate = $invoice->invoice_date;
                $newDate = request('invoice_date');
                $invoice->update([
                    'company_id' => $company->id,
                    'customer_id' => request('customer_id'),
                    'invoice_date' => request('invoice_date'),
                    'due_date' => request('due_date'),
                    'invoice_number' => request('invoice_number'),
                ]);
                $invoice->save();
                $changeDate = $newDate;
                if ($oldDate < $newDate) {
                    $changeDate = $oldDate;
                }
                $salesForUpdate = \DB::table('transactions')->where('company_id', $company->id)->where('type', 'sale')
                    ->where('date', '>=', $changeDate)->orderBy('date', 'asc')->get();
                $invoice->journalEntry()->delete();
                $createInvoice = new CreateInvoice();
                $createInvoice->deleteInvoiceDetails($invoice);
                $createInvoice->updateLines($invoice);
                $createInvoice->updateSales($salesForUpdate);
            });
            return redirect(route('invoices.edit', [$invoice]))
                ->with('status', 'Invoice updated!');
        } catch (\Exception $e) {
            return back()->with('status', $this->translateError($e))->withInput();
        }
    }
    public function destroy(Invoice $invoice)
    {
        try {
            \DB::transaction(function () use ($invoice) {
                $company = \Auth::user()->currentCompany->company;
                $invoiceDate = $invoice->invoice_date;
                $invoice->delete();
                $salesForUpdate = \DB::table('transactions')->where('company_id', $company->id)->where('type', 'sale')
                    ->where('date', '>=', $invoiceDate)->orderBy('date', 'asc')->get();
                $createInvoice = new CreateInvoice();
                $createInvoice->updateSales($salesForUpdate);
            });
            return redirect(route('invoices.index'));
        } catch (\Exception $e) {
            return back()->with('status', $this->translateError($e))->withInput();
        }
    }
}
