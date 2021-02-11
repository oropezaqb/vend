<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CurrentCompany;
use App\Product;
use App\Account;
use App\Http\Requests\StoreProduct;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */

class ProductController extends Controller
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
            $products = \Auth::user()->currentCompany->company->products()->simplePaginate(50);
        } else {
            $products = \Auth::user()->currentCompany->company->products()
                ->where('name', 'like', '%' . request('name') . '%')->get();
        }
        \Request::flash();
        return view('products.index', compact('products'));
    }
    public function show(Product $product)
    {
        return view('products.show', ['product' => $product]);
    }
    public function create()
    {
        $company = \Auth::user()->currentCompany->company;
        $accounts = Account::where('company_id', $company->id)->latest()->get();
        return view('products.create', compact('accounts'));
    }
    public function store(StoreProduct $request)
    {
        try {
            $company = \Auth::user()->currentCompany->company;
            $trackQuantity = false;
            if ($request->input('track_quantity') == 1) {
                $trackQuantity = true;
            }
            $product = new Product([
                'company_id' => $company->id,
                'name' => request('name'),
                'track_quantity' => $trackQuantity,
                'receivable_account_id' => request('receivable_account_id'),
                'inventory_account_id' => request('inventory_account_id'),
                'income_account_id' => request('income_account_id'),
                'expense_account_id' => request('expense_account_id')
            ]);
            $product->save();
            return redirect(route('products.index'));
        } catch (\Exception $e) {
            return back()->with('status', $e->getMessage());
        }
    }
    public function edit(Product $product)
    {
        $company = \Auth::user()->currentCompany->company;
        $accounts = Account::where('company_id', $company->id)->latest()->get();
        return view('products.edit', compact('product', 'accounts'));
    }
    public function update(StoreProduct $request, Product $product)
    {
        try {
            $product->update([
                'name' => $request->input('name'),
                'receivable_account_id' => request('receivable_account_id'),
                'inventory_account_id' => request('inventory_account_id'),
                'income_account_id' => request('income_account_id'),
                'expense_account_id' => request('expense_account_id')
            ]);
            return redirect($product->path());
        } catch (\Exception $e) {
            return back()->with('status', $e->getMessage());
        }
    }
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect(route('products.index'));
    }
    public function import()
    {
        return view('products.import');
    }
    public function upload()
    {
        try {
            \DB::transaction(function () {
                $extension = request()->file('products')->getClientOriginalExtension();
                $filename = uniqid().'.'.$extension;
                $path = request()->file('products')->storeAs('input/products', $filename);
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
                            $product = new Product([
                                'company_id' => $company->id,
                                'name' => $name
                            ]);
                            $product->save();
                        }
                    }
                }
            });
            return redirect(route('products.index'));
        } catch (\Exception $e) {
            return back()->with('status', $this->translateError($e));
        }
    }
    public function translateError($e)
    {
        switch ($e->getCode()) {
            case '23000':
                return "One or more of the products are already recorded.";
        }
        return $e->getMessage();
    }
}
