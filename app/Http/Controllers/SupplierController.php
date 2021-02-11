<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CurrentCompany;
use App\Supplier;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('web');
        $this->middleware('company');
    }
    public function index()
    {
        if (empty(request('name'))) {
            $suppliers = \Auth::user()->currentCompany->company->suppliers()->simplePaginate(50);
        } else {
            $suppliers = \Auth::user()->currentCompany->company->suppliers()
                ->where('name', 'like', '%' . request('name') . '%')->get();
        }
        \Request::flash();
        return view('suppliers.index', compact('suppliers'));
    }
    public function show(Supplier $supplier)
    {
        return view('suppliers.show', ['supplier' => $supplier]);
    }
    public function create()
    {
        return view('suppliers.create');
    }
    public function store()
    {
        try {
            $this->validateSupplier();
            $company = \Auth::user()->currentCompany->company;
            $supplier = new Supplier([
                'company_id' => $company->id,
                'name' => request('name')
            ]);
            $supplier->save();
            return redirect(route('suppliers.index'));
        } catch (\Exception $e) {
            return back()->with('status', $e->getMessage());
        }
    }
    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }
    public function update(Supplier $supplier)
    {
        try {
            $supplier->update($this->validateSupplier());
            return redirect($supplier->path());
        } catch (\Exception $e) {
            return back()->with('status', $e->getMessage());
        }
    }
    public function validateSupplier()
    {
        return request()->validate([
            'name' => 'required'
        ]);
    }
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect(route('suppliers.index'));
    }
    public function import()
    {
        return view('suppliers.import');
    }
    public function upload()
    {
        try {
            \DB::transaction(function () {
                $extension = request()->file('suppliers')->getClientOriginalExtension();
                $filename = uniqid().'.'.$extension;
                $path = request()->file('suppliers')->storeAs('input/suppliers', $filename);
                $csv = array_map('str_getcsv', file(base_path() . "/storage/app/" . $path));
                $messages = array();
                //$error = false;
                $company = \Auth::user()->currentCompany->company;
                $count = count($csv);
                for ($row = 0; $row < $count; $row++) {
                    if ($row > 0) {
                        $name = $csv[$row][0];
                        if (is_null($name)) {
                            $messages[] = 'Line ' . ($row + 1) . ' is blank.';
                            //$error = true;
                        } else {
                            $supplier = new Supplier([
                                'company_id' => $company->id,
                                'name' => $name
                            ]);
                            $supplier->save();
                        }
                    }
                }
            });
            return redirect(route('suppliers.index'));
        } catch (\Exception $e) {
            return back()->with('status', $this->translateError($e));
        }
    }
    public function translateError($e)
    {
        switch ($e->getCode()) {
            case '23000':
                return "One or more of the suppliers are already recorded.";
        }
        return $e->getMessage();
    }
}
