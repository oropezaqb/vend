<?php

namespace App\Http\Controllers;

use App\ReportLineItem;
use Illuminate\Http\Request;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */

class ReportLineItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('company');
        $this->middleware('web');
    }
    public function index()
    {
        //if (!empty(\Auth::user()->currentCompany->company))
        //{

        $company = \Auth::user()->currentCompany->company;
        if (empty(request('line_item'))) {
            $reportLineItems = ReportLineItem::where('company_id', $company->id)->latest()->get();
        } else {
            $reportLineItems = ReportLineItem::where('company_id', $company->id)
                ->where('line_item', 'like', '%' . request('line_item') . '%')->get();
        }
        if (\Route::currentRouteName() === 'report_line_items.index') {
            \Request::flash();
        }
        return view('report_line_items.index', compact('reportLineItems'));

        //}
        //else
        //{
        //    return redirect(route('current_company.index'));
        //}
    }
    public function show(ReportLineItem $reportLineItem)
    {
        return view('report_line_items.show', compact('reportLineItem'));
    }
    public function create()
    {
        if (\Route::currentRouteName() === 'report_line_items.create') {
            \Request::flash();
        }
        return view('report_line_items.create');
    }
    public function store()
    {
        $this->validateReportLineItem();
        $company = \Auth::user()->currentCompany->company;
        $reportLineItem = new ReportLineItem([
            'company_id' => $company->id,
            'report' => request('report'),
            'section' => request('section'),
            'line_item' => request('line_item')
        ]);
        $reportLineItem->save();
        return redirect(route('report_line_items.index'));
    }
    public function edit(ReportLineItem $reportLineItem)
    {
        if (\Route::currentRouteName() === 'report_line_items.edit') {
            \Request::flash();
        }
        return view('report_line_items.edit', compact('reportLineItem'));
    }
    public function update(ReportLineItem $reportLineItem)
    {
        $this->validateReportLineItem();
        $reportLineItem->update([
            'report' => request('report'),
            'section' => request('section'),
            'line_item' => request('line_item')
        ]);
        return redirect($reportLineItem->path());
    }
    public function validateReportLineItem()
    {
        return request()->validate([
            'report' => 'required',
            'line_item' => 'required'
        ]);
    }
    public function destroy(ReportLineItem $reportLineItem)
    {
        $reportLineItem->delete();
        return redirect(route('report_line_items.index'));
    }
}
