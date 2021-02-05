<?php

namespace App\Http\Controllers;

use App\SupplierCredit;
use Illuminate\Http\Request;
use App\Supplier;
use App\Account;
use App\Product;
use Illuminate\Support\Facades\Validator;
use JavaScript;
use App\SupplierCreditCLine;
use App\SupplierCreditILine;
use App\Http\Requests\StoreSupplierCredit;
use App\Jobs\CreateSupplierCredit;
use App\Invoice;
use App\Jobs\CreateInvoice;
use App\Jobs\UpdateSales;
use App\Bill;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */

class SupplierCreditController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $company = \Auth::user()->currentCompany->company;
        if (empty(request('supplier_name'))) {
            $supplierCredits = SupplierCredit::where('company_id', $company->id)->latest()->get();
        } else {
            $supplier = Supplier::where('name', request('supplier_name'))->firstOrFail();
            $supplierCredits = SupplierCredit::where('company_id', $company->id)
                ->where('supplier_id', $supplier->id)->latest()->get();
        }
        if (\Route::currentRouteName() === 'suppliercredit.index') {
            \Request::flash();
        }
        return view('suppliercredit.index', compact('supplierCredits'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $supplierCredit = null;
        $company = \Auth::user()->currentCompany->company;
        $suppliers = Supplier::where('company_id', $company->id)->latest()->get();
        $accounts = Account::where('company_id', $company->id)->latest()->get();
        $products = Product::where('company_id', $company->id)->latest()->get();
        return view(
            'suppliercredit.create',
            compact('supplierCredit', 'suppliers', 'accounts', 'products')
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSupplierCredit $request)
    {
        try {
            \DB::transaction(function () use ($request) {
                $company = \Auth::user()->currentCompany->company;
                $purchasableDoc = $request->input('purchasable_doc');
                $docId = $request->input('doc_id');
                $document = null;
                switch ($purchasableDoc) {
                    case 'Bill':
                        $document = Bill::where('company_id', $company->id)->where('id', $docId)->first();
                        break;
                    case 'Cheque':
                        $document = Cheque::where('company_id', $company->id)->where('id', $docId)->first();
                        break;
                    default:
                        $document = null;
                }
                $supplierCredit = new SupplierCredit([
                    'company_id' => $company->id,
                    'date' => request('date'),
                    'number' => request('number'),
                ]);
                $document->supplierCredits()->save($supplierCredit);
                $createSupplierCredit = new CreateSupplierCredit();
                $createSupplierCredit->updateLines($supplierCredit);
                $salesForUpdate = \DB::table('transactions')->where('company_id', $company->id)
                    ->where('date', '>=', request('date'))->orderBy('date', 'asc')->get();
                $updateSales = new UpdateSales();
                $updateSales->updateSales($salesForUpdate);
            });
            return redirect(route('suppliercredit.index'));
        } catch (\Exception $e) {
            return back()->with('status', $this->translateError($e))->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SupplierCredit  $supplierCredit
     * @return \Illuminate\Http\Response
     */
    public function show(SupplierCredit $supplierCredit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SupplierCredit  $supplierCredit
     * @return \Illuminate\Http\Response
     */
    public function edit(SupplierCredit $supplierCredit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SupplierCredit  $supplierCredit
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SupplierCredit $supplierCredit)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SupplierCredit  $supplierCredit
     * @return \Illuminate\Http\Response
     */
    public function destroy(SupplierCredit $supplierCredit)
    {
        //
    }
}
