<?php

namespace App\Http\Controllers;

use App\LineItem;
use Illuminate\Http\Request;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.NumberOfChildren)
     */

class LineItemController extends Controller
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
        if (empty(request('name'))) {
            $lineItems = LineItem::where('company_id', $company->id)->latest()->get();
        } else {
            $lineItems = LineItem::where('company_id', $company->id)
                ->where('name', 'like', '%' . request('name') . '%')->get();
        }
        if (\Route::currentRouteName() === 'line_items.index') {
            \Request::flash();
        }
        return view('line_items.index', compact('lineItems'));
    }
    public function show(LineItem $lineItem)
    {
        return view('line_items.show', compact('lineItem'));
    }
    public function create()
    {
        if (\Route::currentRouteName() === 'line_items.create') {
            \Request::flash();
        }
        return view('line_items.create');
    }
    public function store()
    {
        $this->validateLineItem();
        $company = \Auth::user()->currentCompany->company;
        $lineItem = new LineItem([
            'company_id' => $company->id,
            'name' => request('name')
        ]);
        $lineItem->save();
        return redirect(route('line_items.index'));
    }
    public function edit(LineItem $lineItem)
    {
        if (\Route::currentRouteName() === 'line_items.edit') {
            \Request::flash();
        }
        return view('line_items.edit', compact('lineItem'));
    }
    public function update(LineItem $lineItem)
    {
        $lineItem->update($this->validateLineItem());
        return redirect($lineItem->path());
    }
    public function validateLineItem()
    {
        return request()->validate([
            'name' => 'required'
        ]);
    }
    public function destroy(LineItem $lineItem)
    {
        try {
            $lineItem->delete();
            return redirect(route('line_items.index'));
        } catch (\Illuminate\Database\QueryException $ex) {
            return back()->with('status', $ex->getMessage());
        }
    }
}