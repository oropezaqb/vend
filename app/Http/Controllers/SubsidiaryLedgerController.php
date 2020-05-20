<?php

namespace App\Http\Controllers;

use App\SubsidiaryLedger;
use Illuminate\Http\Request;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */

class SubsidiaryLedgerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('company');
        $this->middleware('web');
    }
    public function index()
    {
        //if (!empty(\Auth::user()->current_company->company))
        //{

        $company = \Auth::user()->current_company->company;
        if (empty(request('name'))) {
            $subsidiaryLedgers = SubsidiaryLedger::where('company_id', $company->id)->latest()->get();
        } else {
            $subsidiaryLedgers = SubsidiaryLedger::where('company_id', $company->id)
                ->where('name', 'like', '%' . request('name') . '%')->get();
        }
        if (\Route::currentRouteName() === 'subsidiary_ledgers.index') {
            \Request::flash();
        }
        return view('subsidiary_ledgers.index', compact('subsidiaryLedgers'));

        //}
        //else
        //{
        //    return redirect(route('current_company.index'));
        //}
    }
    public function show(SubsidiaryLedger $subsidiaryLedger)
    {
        return view('subsidiary_ledgers.show', compact('subsidiaryLedger'));
    }
    public function create()
    {
        if (\Route::currentRouteName() === 'subsidiary_ledgers.create') {
            \Request::flash();
        }
        return view('subsidiary_ledgers.create');
    }
    public function store()
    {
        $this->validateSubsidiaryLedger();
        $company = \Auth::user()->current_company->company;
        $subsidiaryLedger = new SubsidiaryLedger([
            'company_id' => $company->id,
            'number' => request('number'),
            'name' => request('name')
        ]);
        $subsidiaryLedger->save();
        return redirect(route('subsidiary_ledgers.index'));
    }
    public function edit(SubsidiaryLedger $subsidiaryLedger)
    {
        if (\Route::currentRouteName() === 'subsidiary_ledgers.edit') {
            \Request::flash();
        }
        return view('subsidiary_ledgers.edit', compact('subsidiaryLedger'));
    }
    public function update(SubsidiaryLedger $subsidiaryLedger)
    {
        $subsidiaryLedger->update($this->validateSubsidiaryLedger());
        return redirect($subsidiaryLedger->path());
    }
    public function validateSubsidiaryLedger()
    {
        return request()->validate([
            'number' => 'required',
            'name' => 'required'
        ]);
    }
    public function destroy(SubsidiaryLedger $subsidiaryLedger)
    {
        $subsidiaryLedger->delete();
        return redirect(route('subsidiary_ledgers.index'));
    }
}
