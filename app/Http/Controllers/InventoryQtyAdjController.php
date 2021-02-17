<?php

namespace App\Http\Controllers;

use App\InventoryQtyAdj;
use Illuminate\Http\Request;
use App\Account;
use App\Product;
use Illuminate\Support\Facades\Validator;
use JavaScript;
use App\InventoryQtyAdjLine;
use App\Http\Requests\StoreInventoryQtyAdj;
use App\Jobs\CreateInventoryQtyAdj;
use App\Jobs\UpdateSales;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UndefinedVariable)
     * @SuppressWarnings(PHPMD.ShortVariableName)
     */

class InventoryQtyAdjController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('company');
        $this->middleware('web');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $company = \Auth::user()->currentCompany->company;
        if (empty(request('product_name'))) {
            $inventoryQtyAdjs = InventoryQtyAdj::where('company_id', $company->id)->latest()->get();
        } else {
            $product = Product::where('name', request('product_name'))->firstOrFail();
            $inventoryQtyAdjs = InventoryQtyAdj::where('company_id', $company->id)
                ->where('product_id', $product->id)->latest()->get();
        }
        if (\Route::currentRouteName() === 'inventory_qty_adjs.index') {
            \Request::flash();
        }
        return view('inventory_qty_adjs.index', compact('inventoryQtyAdjs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $company = \Auth::user()->currentCompany->company;
        $accounts = Account::where('company_id', $company->id)->latest()->get();
        $products = Product::where('company_id', $company->id)->latest()->get();
        $inventoryShrinkageAcct = Account::firstOrCreate(['title' 
            => 'Inventory Shrinkage', 'company_id' => $company->id], ['number' 
            => '510', 'subsidiary_ledger' => false]);
        return view(
            'inventory_qty_adjs.create',
            compact('accounts', 'products', 'inventoryShrinkageAcct')
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreInventoryQtyAdj $request)
    {
        //try {
            \DB::transaction(function () use ($request) {
                $company = \Auth::user()->currentCompany->company;
                $inventoryQtyAdj = new InventoryQtyAdj([
                    'company_id' => $company->id,
                    'date' => request('date'),
                    'number' => request('number'),
                    'account_id' => request('account_id'),
                ]);
                $inventoryQtyAdj->save();
                $createInventoryQtyAdj = new CreateInventoryQtyAdj();
                $createInventoryQtyAdj->updateLines($inventoryQtyAdj);
                $createInventoryQtyAdj->recordTransaction($inventoryQtyAdj);
                $salesForUpdate = \DB::table('transactions')->where('company_id', $company->id)
                    ->where('date', '>=', request('date'))->orderBy('date', 'asc')->get();
                $updateSales = new UpdateSales();
                $updateSales->updateSales($salesForUpdate);
            });
            return redirect(route('inventory_qty_adjs.index'));
        //} catch (\Exception $e) {
        //    return back()->with('status', $this->translateError($e))->withInput();
        //}
    }
    public function translateError($e)
    {
        switch ($e->getCode()) {
            case '23000':
                if (preg_match(
                    "/for key '(.*)'/",
                    $e->getMessage(),
                    $m
                )) {
                    $indexes = array(
                      'my_unique_ref' =>
                    array ('Supplier credit is already recorded.', 'number'));
                    if (isset($indexes[$m[1]])) {
                        $this->err_flds = array($indexes[$m[1]][1] => 1);
                        return $indexes[$m[1]][0];
                    }
                }
                break;
        }
        return $e->getMessage();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\InventoryQtyAdj  $inventoryQtyAdj
     * @return \Illuminate\Http\Response
     */
    public function show(InventoryQtyAdj $inventoryQtyAdj)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\InventoryQtyAdj  $inventoryQtyAdj
     * @return \Illuminate\Http\Response
     */
    public function edit(InventoryQtyAdj $inventoryQtyAdj)
    {
        $company = \Auth::user()->currentCompany->company;
        $accounts = Account::where('company_id', $company->id)->latest()->get();
        $products = Product::where('company_id', $company->id)->latest()->get();
        return view(
            'inventory_qty_adjs.edit',
            compact('inventoryQtyAdj', 'accounts', 'products')
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\InventoryQtyAdj  $inventoryQtyAdj
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, InventoryQtyAdj $inventoryQtyAdj)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\InventoryQtyAdj  $inventoryQtyAdj
     * @return \Illuminate\Http\Response
     */
    public function destroy(InventoryQtyAdj $inventoryQtyAdj)
    {
        $inventoryQtyAdj->delete();
        return redirect(route('inventory_qty_adjs.index'));
    }
}
