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
                $this->updateLines($invoice);
                $createInvoice = new CreateInvoice();
                $createInvoice->recordTransaction($invoice);
                $salesForUpdate = \DB::table('transactions')->where('company_id', $company->id)->where('type', 'sale')
                    ->where('date', '>=', request('invoice_date'))->orderBy('date', 'asc')->get();
                foreach($salesForUpdate as $saleForUpdate)
                {
                    $transactions = Transaction::all();
                    $transaction = $transactions->find($saleForUpdate->id);
                    $invoice = $transaction->transactable;
                    if (is_object($invoice->journalEntry))
                    {
                        foreach($invoice->journalEntry->postings as $posting)
                        {
                            $posting->delete();
                        }
                        $invoice->journalEntry->delete();
                    }
                    if (is_object($invoice->sales))
                    {
                        $sales = $invoice->sales;
                        foreach($sales as $sale)
                        {
                            $sale->delete();
                        }
                    }
                }
                foreach($salesForUpdate as $saleForUpdate)
                {
                    $transactions = Transaction::all();
                    $transaction = $transactions->find($saleForUpdate->id);
                    $invoice = $transaction->transactable;
                    $input = array();
                    $row = 0;
                    $input['customer_id'] = $invoice->customer_id;
                    $input['invoice_date'] = $invoice->invoice_date;
                    $input['invoice_number'] = $invoice->invoice_number;
                    foreach($invoice->itemLines as $itemLine)
                    {
                        $input['item_lines']["'product_id'"][$row] = $itemLine->product_id;
                        $input['item_lines']["'description'"][$row] = $itemLine->description;
                        $input['item_lines']["'quantity'"][$row] = $itemLine->quantity;
                        $input['item_lines']["'amount'"][$row] = $itemLine->amount;
                        $input['item_lines']["'output_tax'"][$row] = $itemLine->output_tax;
                        $row += 1;
                    }
                    $createInvoice = new CreateInvoice();
                    $createInvoice->recordSales($invoice, $input);
                    $createInvoice->recordJournalEntry($invoice, $input);
                }
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
