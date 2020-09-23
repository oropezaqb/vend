<?php

namespace App\Http\Controllers;

use App\Customer;
use Illuminate\Http\Request;
use App\CurrentCompany;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('web');
        $this->middleware('company');
    }
    public function index()
    {
        if (empty(request('name')))
        {
            $customers = \Auth::user()->currentCompany->company->customers()->simplePaginate(50);
        }
        else
        {
            $customers = \Auth::user()->currentCompany->company->customers()
                ->where('name', 'like', '%' . request('name') . '%')->get();
        }
        \Request::flash();
        return view('customers.index', compact('customers'));
    }
    public function show(Customer $customer)
    {
        return view('customers.show', ['customer' => $customer]);
    }
    public function create()
    {
        return view('customers.create');
    }
    public function store()
    {
        try {
            $this->validateCustomer();
            $company = \Auth::user()->currentCompany->company;
            $customer = new Customer([
                'company_id' => $company->id,
                'name' => request('name')
            ]);
            $customer->save();
            return redirect(route('customers.index'));
        } catch (\Exception $e) {
            return back()->with('status', $e->getMessage());
        }
    }
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }
    public function update(Customer $customer)
    {
        try {
            $customer->update($this->validateCustomer());
            return redirect($customer->path());
        } catch (\Exception $e) {
            return back()->with('status', $e->getMessage());
        }
    }
    public function validateCustomer()
    {
        return request()->validate([
            'name' => 'required'
        ]);
    }
    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect(route('customers.index'));
    }
    public function translateError($e)
    {
        switch ($e->getCode()) {
            case '23000':
                return "One or more of the customers are already recorded.";
        }
        return $e->getMessage();
    }
}
