<?php

namespace App\Http\Controllers;

use App\CreditNote;
use Illuminate\Http\Request;
use App\Customer;
use App\Product;
use Illuminate\Support\Facades\Validator;
use JavaScript;
use App\CreditNoteLine;
use App\Http\Requests\StoreCreditNote;
use App\Jobs\CreateCreditNote;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UndefinedVariable)
     * @SuppressWarnings(PHPMD.ShortVariableName)
     */

class CreditNote extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('company');
        $this->middleware('web');
        $this->middleware('outputVat');
    }
    public function index()
    {
        $company = \Auth::user()->currentCompany->company;
        if (empty(request('customer_name')))
        {
            $creditNotes = CreditNote::where('company_id', $company->id)->latest()->get();
        }
        else
        {
            $customer = Customer::where('name', request('customer_name'))->firstOrFail();
            $creditNotes = CreditNote::where('company_id', $company->id)
                ->where('customer_id', $customer->id)->latest()->get();
        }
        if (\Route::currentRouteName() === 'creditnote.index')
        {
            \Request::flash();
        }
        return view('creditnote.index', compact('creditNotes'));
    }
    public function show(CreditNote $creditNote)
    {
        $company = \Auth::user()->currentCompany->company;
        $customers = Customer::where('company_id', $company->id)->latest()->get();
        $products = Product::where('company_id', $company->id)->latest()->get();
        return view('creditnote.show',
            compact('creditNote', 'customers', 'products'));
    }
    public function create()
    {
        $company = \Auth::user()->currentCompany->company;
        $customers = Customer::where('company_id', $company->id)->latest()->get();
        $products = Product::where('company_id', $company->id)->latest()->get();
        return view('sales_receipts.create',
            compact('customers', 'products'));
    }
    public function store(StoreCreditNote $request)
    {
        try {
            \DB::transaction(function () use ($request) {
                $company = \Auth::user()->currentCompany->company;
                $creditNote = new CreditNote([
                    'company_id' => $company->id,
                    'customer_id' => request('customer_id'),
                    'date' => request('date'),
                    'number' => request('number'),
                ]);
                $creditNote->save();
                $createCreditNote = new CreateCreditNote();
                $createCreditNote->updateLines($creditNote);
                $createCreditNote->recordTransaction($creditNote);
                $salesForUpdate = \DB::table('transactions')->where('company_id', $company->id)->where('type', 'sale')
                    ->where('date', '>=', request('date'))->orderBy('date', 'asc')->get();
                $createCreditNote->updateSales($salesForUpdate);
            });
            return redirect(route('creditnote.index'));
        } catch (\Exception $e) {
            return back()->with('status', $this->translateError($e))->withInput();
        }
    }
    public function translateError($e)
    {
        switch ($e->getCode()) {
        case '23000':
            if (preg_match("/for key '(.*)'/",
              $e->getMessage(), $m)) {
                $indexes = array(
                  'my_unique_ref' =>
                    array ('Credit note is already recorded.', 'number'));
                if (isset($indexes[$m[1]])) {
                    $this->err_flds = array($indexes[$m[1]][1] => 1);
                    return $indexes[$m[1]][0];
                }
            }
        break;
        }
        return $e->getMessage();
    }

    public function edit(CreditNote $creditNote)
    {
        $company = \Auth::user()->currentCompany->company;
        $customers = Customer::where('company_id', $company->id)->latest()->get();
        $products = Product::where('company_id', $company->id)->latest()->get();
        return view('creditnote.edit',
            compact('creditNote', 'customers', 'products'));
    }
    public function update(StoreCreditNote $request, CreditNote $creditNote)
    {
        try {
            \DB::transaction(function () use ($request, $creditNote) {
                $company = \Auth::user()->currentCompany->company;
                $oldDate = $creditNote->date;
                $newDate = request('date');
                $creditNote->update([
                    'company_id' => $company->id,
                    'customer_id' => request('customer_id'),
                    'date' => request('date'),
                    'number' => request('number'),
                ]);
                $creditNote->save();
                $changeDate = $newDate;
                if ($oldDate < $newDate)
                {
                    $changeDate = $oldDate;
                }
                $salesForUpdate = \DB::table('transactions')->where('company_id', $company->id)->where('type', 'sale')->where('date', '>=', $changeDate)->orderBy('date', 'asc')->get();
                $creditNote->journalEntry()->delete();
                $createCreditNote = new CreateCreditNote();
                $createCreditNote->deleteCreditNote($creditNote);
                $createCreditNote->updateLines($creditNote);
                $createCreditNote->updateSales($salesForUpdate);
            });
            return redirect(route('creditnote.edit', [$creditNote]))
                ->with('status', 'Credit note updated!');
        } catch (\Exception $e) {
            return back()->with('status', $this->translateError($e))->withInput();
        }
    }
    public function destroy(CreditNote $creditNote)
    {
        try {
            \DB::transaction(function () use ($creditNote) {
                $company = \Auth::user()->currentCompany->company;
                $creditNoteDate = $creditNote->date;
                $creditNote->delete();
                $salesForUpdate = \DB::table('transactions')->where('company_id', $company->id)->where('type', 'sale')
                    ->where('date', '>=', $creditNoteDate)->orderBy('date', 'asc')->get();
                $createCreditNote = new CreateCreditNote();
                $createCreditNote->updateSales($salesForUpdate);
            });
            return redirect(route('creditnote.index'));
        } catch (\Exception $e) {
            return back()->with('status', $this->translateError($e))->withInput();
        }
    }
}
