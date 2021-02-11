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
use App\Jobs\UpdateSales;
use App\Bill;
use App\Jobs\SaveSCLines;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.ShortVariableName)
     * @SuppressWarnings(PHPMD.UndefinedVariable)
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
        //try {
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
                $saveSCLines = new SaveSCLines();
                $saveSCLines->updateLines($supplierCredit, $document);
                $createSupplierCredit = new CreateSupplierCredit();
                $createSupplierCredit->savePurchaseReturns($supplierCredit, $document);
                $createSupplierCredit->deletePurchases($supplierCredit, $document);
                $createSupplierCredit->updatePurchases($supplierCredit, $document);
                $createSupplierCredit->recordJournalEntry($supplierCredit);
                $salesForUpdate = \DB::table('transactions')->where('company_id', $company->id)
                    ->where('date', '>=', request('date'))->orderBy('date', 'asc')->get();
                $updateSales = new UpdateSales();
                $updateSales->updateSales($salesForUpdate);
            });
            return redirect(route('suppliercredit.index'));
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
     * @param  \App\SupplierCredit  $supplierCredit
     * @return \Illuminate\Http\Response
     */
    public function show(SupplierCredit $suppliercredit)
    {
        $supplierCredit = $suppliercredit;
        $company = \Auth::user()->currentCompany->company;
        $suppliers = Supplier::where('company_id', $company->id)->latest()->get();
        $accounts = Account::where('company_id', $company->id)->latest()->get();
        $products = Product::where('company_id', $company->id)->latest()->get();
        return view(
            'suppliercredit.show',
            compact('supplierCredit', 'suppliers', 'accounts', 'products')
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SupplierCredit  $supplierCredit
     * @return \Illuminate\Http\Response
     */
    public function edit(SupplierCredit $suppliercredit)
    {
        $supplierCredit = $suppliercredit;
        $company = \Auth::user()->currentCompany->company;
        $suppliers = Supplier::where('company_id', $company->id)->latest()->get();
        $accounts = Account::where('company_id', $company->id)->latest()->get();
        $products = Product::where('company_id', $company->id)->latest()->get();
        return view(
            'suppliercredit.edit',
            compact('supplierCredit', 'suppliers', 'accounts', 'products')
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SupplierCredit  $supplierCredit
     * @return \Illuminate\Http\Response
     */
    public function update(StoreSupplierCredit $request, SupplierCredit $suppliercredit)
    {
        try {
            \DB::transaction(function () use ($request, $suppliercredit) {
                $supplierCredit = $suppliercredit;
                $company = \Auth::user()->currentCompany->company;
                $oldDocument = $supplierCredit->purchasable;
                $oldDate = $supplierCredit->date;
                $newDate = request('date');
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
                $supplierCredit->update([
                    'company_id' => $company->id,
                    'date' => request('date'),
                    'number' => request('number'),
                ]);
                $document->supplierCredits()->save($supplierCredit);
                $saveSCLines = new SaveSCLines();
                $createSupplierCredit = new CreateSupplierCredit();
                foreach ($supplierCredit->clines as $line) {
                    $line->delete();
                }
                foreach ($supplierCredit->ilines as $line) {
                    $line->delete();
                }
                $saveSCLines->updateLines($supplierCredit, $document);
                $createSupplierCredit->deletePurchaseReturns($supplierCredit, $document);
                $createSupplierCredit->savePurchaseReturns($supplierCredit, $document);
                if (!($oldDocument === $document)) {
                    $createSupplierCredit->deletePurchases($supplierCredit, $oldDocument);
                    $createSupplierCredit->updatePurchases($supplierCredit, $oldDocument);
                }
                $createSupplierCredit->deletePurchases($supplierCredit, $document);
                $createSupplierCredit->updatePurchases($supplierCredit, $document);
                $supplierCredit->journalEntry->delete();
                $createSupplierCredit->recordJournalEntry($supplierCredit);
                $changeDate = $newDate;
                if ($oldDate < $newDate) {
                    $changeDate = $oldDate;
                }
                $salesForUpdate = \DB::table('transactions')->where('company_id', $company->id)
                    ->where('date', '>=', $changeDate)->orderBy('date', 'asc')->get();
                $updateSales = new UpdateSales();
                $updateSales->updateSales($salesForUpdate);
            });
            return redirect(route('suppliercredit.edit', [$suppliercredit]))
                ->with('status', 'Supplier credit updated!');
        } catch (\Exception $e) {
            return back()->with('status', $this->translateError($e))->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SupplierCredit  $supplierCredit
     * @return \Illuminate\Http\Response
     */
    public function destroy(SupplierCredit $suppliercredit)
    {
        $supplierCredit = $suppliercredit;
        //try {
            \DB::transaction(function () use ($supplierCredit) {
                $company = \Auth::user()->currentCompany->company;
                $supplierCreditDate = $supplierCredit->date;
                foreach ($supplierCredit->purchaseReturns as $purchaseReturn) {
                    $purchaseReturn->delete();
                }
                $createSupplierCredit = new CreateSupplierCredit();
                $createSupplierCredit->deletePurchases($supplierCredit, $supplierCredit->purchasable);
                $createSupplierCredit->updatePurchases($supplierCredit, $supplierCredit->purchasable);
                $supplierCredit->journalEntry->delete();
                $supplierCredit->delete();
                $salesForUpdate = \DB::table('transactions')->where('company_id', $company->id)
                    ->where('date', '>=', $supplierCreditDate)->orderBy('date', 'asc')->get();
                $updateSales = new UpdateSales();
                $updateSales->updateSales($salesForUpdate);
            });
            return redirect(route('suppliercredit.index'));
        //} catch (\Exception $e) {
        //    return back()->with('status', $this->translateError($e))->withInput();
        //}
    }
}
