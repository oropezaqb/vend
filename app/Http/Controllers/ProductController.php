<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CurrentCompany;
use App\Product;

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
        return view('products.create');
    }
    public function store()
    {
        try {
            $this->validateProduct();
            $company = \Auth::user()->currentCompany->company;
            $trackQuantity = false;
            if (request('track_quantity') == 1) {
                $trackQuantity = true;
            }
            $product = new Product([
                'company_id' => $company->id,
                'name' => request('name'),
                'track_quantity' => $trackQuantity
            ]);
            $product->save();
            return redirect(route('products.index'));
        } catch (\Exception $e) {
            return back()->with('status', $e->getMessage());
        }
    }
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }
    public function update(Product $product)
    {
        try {
            $this->validateProduct();
            $trackQuantity = true;
            if (empty(request('track_quantity'))) {
                $trackQuantity = false;
            }
            $product->update([
                'name' => request('name'),
                'track_quantity' => $trackQuantity
            ]);
            return redirect($product->path());
        } catch (\Exception $e) {
            return back()->with('status', $e->getMessage());
        }
    }
    public function validateProduct()
    {
        return request()->validate([
            'name' => 'required'
        ]);
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
                $error = false;
                $company = \Auth::user()->currentCompany->company;
                $count = count($csv);
                for ($row = 0; $row < $count; $row++) {
                    if ($row > 0) {
                        $name = $csv[$row][0];
                        if (is_null($name)) {
                            $messages[] = 'Line ' . ($row + 1) . ' is blank.';
                            $error = true;
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