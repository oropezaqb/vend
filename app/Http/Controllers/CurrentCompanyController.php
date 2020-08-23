<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Company;
use App\CurrentCompany;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */

class CurrentCompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('web');
    }
    public function index()
    {
        $currentCompany = \Auth::user()->currentCompany;
        return view('current_company.index', ['currentCompany' => $currentCompany]);
    }
    public function show(CurrentCompany $currentCompany)
    {
        return view('current_company.show', ['currentCompany' => $currentCompany]);
    }
    public function create()
    {
        $companies = \Auth::user()->companies;
        return view('current_company.create', ['companies' => $companies]);
    }
    public function store()
    {
        $this->validateCurrentCompany();
        $company = Company::where('id', request('company_id'))->firstOrFail();
        $user = \Auth::user();
        if (empty($user->currentCompany)) {
            $currentCompany = new CurrentCompany(['user_id' => $user->id, 'company_id' => $company->id]);
            $currentCompany->save();
            return redirect(route('home'))
                ->with('status', 'Welcome! You may now start adding items through the navigation pane.');
        } else {
            $companies = \Auth::user()->companies;
            \Request::flash();
            $message = "Cannot add another company as current. You may update your current company instead.";
            return view('current_company.create', ['message' => $message, 'companies' => $companies]);
        }
    }
    public function edit(CurrentCompany $currentCompany)
    {
        $companies = \Auth::user()->companies()->latest()->get();
        return view('current_company.edit', ['currentCompany' => $currentCompany, 'companies' => $companies]);
    }
    public function update(CurrentCompany $currentCompany)
    {
        $currentCompany->update($this->validateCurrentCompany());
        return redirect($currentCompany->path());
    }
    public function validateCurrentCompany()
    {
        return request()->validate([
            'company_id' => 'required'
        ]);
    }
    public function destroy(CurrentCompany $currentCompany)
    {
        $currentCompany->delete();
        return redirect(route('current_company.index'));
    }
}
