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
            $company = \Auth::user()->currentCompany->company;
            $invoice = new Invoice([
                'company_id' => $company->id,
                'customer_id' => request('customer_id'),
                'invoice_date' => request('invoice_date'),
                'due_date' => request('due_date'),
                'invoice_number' => request('invoice_number'),
            ]);
            $invoice->save();
            $this->updateLines($invoice);
            $createInvoice = new CreateInvoice();
            $input = $request->all();
            $createInvoice->recordSales($invoice, $input);
            $createInvoice->recordJournalEntry($invoice, $input);
            $createInvoice->recordTransaction($invoice);
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
    public function updateLines($invoice)
    {
        if (!is_null(request("item_lines.'product_id'"))) {
            $count = count(request("item_lines.'product_id'"));
            for ($row = 0; $row < $count; $row++)
            {
                $outputTax = 0;
                if (!is_null(request("item_lines.'output_tax'.".$row))) {
                    $outputTax = request("item_lines.'output_tax'.".$row);
                }
                $itemLine = new InvoiceItemLine([
                    'invoice_id' => $invoice->id,
                    'product_id' => request("item_lines.'product_id'.".$row),
                    'description' => request("item_lines.'description'.".$row),
                    'quantity' => request("item_lines.'quantity'.".$row),
                    'amount' => request("item_lines.'amount'.".$row),
                    'output_tax' => $outputTax
                ]);
                $itemLine->save();
            }
        }
    }
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect(route('invoices.index'));
    }
}
