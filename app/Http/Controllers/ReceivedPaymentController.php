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
}
