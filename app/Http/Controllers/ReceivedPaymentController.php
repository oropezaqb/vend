<?php

namespace App\Http\Controllers;

use App\ReceivedPayment;
use Illuminate\Http\Request;
use App\Customer;
use App\Account;
use App\Invoice;
use Illuminate\Support\Facades\Validator;
use JavaScript;
use App\ReceivedPaymentLine;
use App\Http\Requests\StoreReceivedPayment;
use App\Jobs\CreateReceivedPayment;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UndefinedVariable)
     * @SuppressWarnings(PHPMD.ShortVariableName)
     */

class ReceivedPaymentController extends Controller
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
        if (empty(request('customer_name')))
        {
            $receivedPayments = ReceivedPayment::where('company_id', $company->id)->latest()->get();
        }
        else
        {
            $customer = Customer::where('name', request('customer_name'))->firstOrFail();
            $receivedPayments = ReceivedPayment::where('company_id', $company->id)
                ->where('customer_id', $customer->id)->latest()->get();
        }
        if (\Route::currentRouteName() === 'received_payments.index')
        {
            \Request::flash();
        }
        return view('received_payments.index', compact('receivedPayments'));
    }
    public function create()
    {
        $company = \Auth::user()->currentCompany->company;
        $customers = Customer::where('company_id', $company->id)->latest()->get();
        $accounts = Account::where('company_id', $company->id)->latest()->get();
        return view('received_payments.create',
            compact('customers', 'accounts'));
    }
    public function store(StoreReceivedPayment $request)
    {
        $company = \Auth::user()->currentCompany->company;
        $receivedPayment = new ReceivedPayment([
            'company_id' => $company->id,
            'date' => request('date'),
            'customer_id' => request('customer_id'),
            'number' => request('number'),
            'account_id' => request('account_id')
        ]);
        $receivedPayment->save();
        if (!is_null(request("item_lines.'invoice_id'"))) {
            $count = count(request("item_lines.'invoice_id'"));
            for ($row = 0; $row < $count; $row++) {
                if (is_numeric(request("item_lines.'payment'.".$row)) && request("item_lines.'payment'.".$row) > 0) {
                    $receivedPaymentLine = new ReceivedPaymentLine([
                        'company_id' => $company->id,
                        'received_payment_id' => $receivedPayment->id,
                        'invoice_id' => request("item_lines.'invoice_id'.".$row),
                        'amount' => request("item_lines.'payment'.".$row)
                    ]);
                    $receivedPaymentLine->save();
                }
            }
        }
        return redirect(route('received_payments.index'));
    }
}
