<?php

namespace App\Http\Controllers;

use App\Bill;
use Illuminate\Http\Request;
use App\Supplier;
use App\Account;
use App\Product;
use Illuminate\Support\Facades\Validator;
use JavaScript;
use App\BillCategoryLine;
use App\BillItemLine;
use App\Http\Requests\StoreBill;
use App\Purchase;
use App\Document;
use App\JournalEntry;
use App\Posting;
use App\SubsidiaryLedger;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UndefinedVariable)
     * @SuppressWarnings(PHPMD.ShortVariableName)
     */

class BillController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('company');
        $this->middleware('web');
        $this->middleware('accountsPayable');
        $this->middleware('inputVat');
    }
    public function index()
    {
        $company = \Auth::user()->currentCompany->company;
        if (empty(request('supplier_name'))) {
            $bills = Bill::where('company_id', $company->id)->latest()->get();
        } else {
            $supplier = Supplier::where('name', request('supplier_name'))->firstOrFail();
            $bills = Bill::where('company_id', $company->id)
                ->where('supplier_id', $supplier->id)->latest()->get();
        }
        if (\Route::currentRouteName() === 'bills.index') {
            \Request::flash();
        }
        return view('bills.index', compact('bills'));
    }
    public function show(Bill $bill)
    {
        $company = \Auth::user()->currentCompany->company;
        $suppliers = Supplier::where('company_id', $company->id)->latest()->get();
        $accounts = Account::where('company_id', $company->id)->latest()->get();
        $products = Product::where('company_id', $company->id)->latest()->get();
        return view(
            'bills.show',
            compact('bill', 'suppliers', 'accounts', 'products')
        );
    }
    public function create()
    {
        $company = \Auth::user()->currentCompany->company;
        $suppliers = Supplier::where('company_id', $company->id)->latest()->get();
        $accounts = Account::where('company_id', $company->id)->latest()->get();
        $products = Product::where('company_id', $company->id)->latest()->get();
        return view(
            'bills.create',
            compact('suppliers', 'accounts', 'products')
        );
    }
    public function store(StoreBill $request)
    {
        try {
            $company = \Auth::user()->currentCompany->company;
            $bill = new Bill([
                'company_id' => $company->id,
                'supplier_id' => request('supplier_id'),
                'bill_date' => request('bill_date'),
                'due_date' => request('due_date'),
                'bill_number' => request('bill_number'),
            ]);
            $bill->save();
            $this->updateLines($bill);
            $this->recordPurchases($bill);
            $this->recordJournalEntry($bill);
            return redirect(route('bills.index'));
        } catch (\Exception $e) {
            return back()->with('status', $this->translateError($e))->withInput();
        }
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
                    array ('Bill is already recorded.', 'bill_number'));
                    if (isset($indexes[$m[1]])) {
                        $this->err_flds = array($indexes[$m[1]][1] => 1);
                        return $indexes[$m[1]][0];
                    }
                }
                break;
        }
        return $e->getMessage();
    }
    public function edit(Bill $bill)
    {
        $company = \Auth::user()->currentCompany->company;
        $suppliers = Supplier::where('company_id', $company->id)->latest()->get();
        $accounts = Account::where('company_id', $company->id)->latest()->get();
        $products = Product::where('company_id', $company->id)->latest()->get();
        return view(
            'bills.edit',
            compact('bill', 'suppliers', 'accounts', 'products')
        );
    }
    public function update(StoreBill $request, Bill $bill)
    {
        try {
            $company = \Auth::user()->currentCompany->company;
            $bill->update([
                'company_id' => $company->id,
                'supplier_id' => request('supplier_id'),
                'bill_date' => request('bill_date'),
                'due_date' => request('due_date'),
                'bill_number' => request('bill_number'),
            ]);
            $bill->save();
            foreach ($bill->categoryLines as $categoryLine) {
                $categoryLine->delete();
            }
            foreach ($bill->itemLines as $itemLine) {
                $itemLine->delete();
            }
            $this->updateLines($bill);
            return redirect(route('bills.edit', [$bill]))
                ->with('status', 'Bill updated!');
        } catch (\Exception $e) {
            return back()->with('status', $this->translateError($e))->withInput();
        }
    }
    public function updateLines($bill)
    {
        if (!is_null(request("category_lines.'account_id'"))) {
            $count = count(request("category_lines.'account_id'"));
            for ($row = 0; $row < $count; $row++) {
                $inputTax = 0;
                if (!is_null(request("category_lines.'input_tax'.".$row))) {
                    $inputTax = request("category_lines.'input_tax'.".$row);
                }
                $categoryLine = new BillCategoryLine([
                    'bill_id' => $bill->id,
                    'account_id' => request("category_lines.'account_id'.".$row),
                    'description' => request("category_lines.'description'.".$row),
                    'amount' => request("category_lines.'amount'.".$row),
                    'input_tax' => $inputTax
                ]);
                $categoryLine->save();
            }
        }
        if (!is_null(request("item_lines.'product_id'"))) {
            $count = count(request("item_lines.'product_id'"));
            for ($row = 0; $row < $count; $row++) {
                $inputTax = 0;
                if (!is_null(request("item_lines.'input_tax'.".$row))) {
                    $inputTax = request("item_lines.'input_tax'.".$row);
                }
                $itemLine = new BillItemLine([
                    'bill_id' => $bill->id,
                    'product_id' => request("item_lines.'product_id'.".$row),
                    'description' => request("item_lines.'description'.".$row),
                    'quantity' => request("item_lines.'quantity'.".$row),
                    'amount' => request("item_lines.'amount'.".$row),
                    'input_tax' => $inputTax
                ]);
                $itemLine->save();
            }
        }
    }
    public function recordPurchases($bill)
    {
        if (!is_null(request("item_lines.'product_id'"))) {
            $count = count(request("item_lines.'product_id'"));
            for ($row = 0; $row < $count; $row++) {
                $product = Product::find(request("item_lines.'product_id'.".$row));
                if ($product->track_quantity) {
                    $company = \Auth::user()->currentCompany->company;
                    $purchase = new Purchase([
                        'company_id' => $company->id,
                        'date' => request('bill_date'),
                        'product_id' => request("item_lines.'product_id'.".$row),
                        'quantity' => request("item_lines.'quantity'.".$row),
                        'amount' => request("item_lines.'amount'.".$row)
                    ]);
                    $bill->purchases()->save($purchase);
                }
            }
        }
    }
    public function recordJournalEntry($bill)
    {
        $company = \Auth::user()->currentCompany->company;
        $document = Document::firstOrCreate(['name' => 'Bill', 'company_id' => $company->id]);
        $payableAccount = Account::where('title', 'Accounts Payable')->firstOrFail();
        $taxAccount = Account::where('title', 'Input VAT')->firstOrFail();
        $supplier = Supplier::all()->find(request('supplier_id'));
        $payableSubsidiary = SubsidiaryLedger::where('name', $supplier->name)->firstOrCreate(['name' => $supplier->name, 'company_id' => $company->id]);
        $journalEntry = new JournalEntry([
            'company_id' => $company->id,
            'date' => request('bill_date'),
            'document_type_id' => $document->id,
            'explanation' => 'To record purchase of goods on account.'
        ]);
        $bill->journalEntry()->save($journalEntry);
        $payableAmount = 0;
        $taxAmount = 0;
        if (!is_null(request("category_lines.'account_id'"))) {
            $count = count(request("category_lines.'account_id'"));
            for ($row = 0; $row < $count; $row++)
            {
                $inputTax = 0;
                if (!is_null(request("category_lines.'input_tax'.".$row))) {
                    $inputTax = request("category_lines.'input_tax'.".$row);
                }
                $posting = new Posting([
                    'company_id' => $company->id,
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => request("category_lines.'account_id'.".$row),
                    'debit' => request("category_lines.'amount'.".$row)
                ]);
                $posting->save();
                $payableAmount -= request("category_lines.'amount'.".$row) + $inputTax;
                $taxAmount += $inputTax;
            }
        }
        if (!is_null(request("item_lines.'product_id'"))) {
            $count = count(request("item_lines.'product_id'"));
            for ($row = 0; $row < $count; $row++)
            {
                $inputTax = 0;
                if (!is_null(request("item_lines.'input_tax'.".$row))) {
                    $inputTax = request("item_lines.'input_tax'.".$row);
                }
                $product = Product::find(request("item_lines.'product_id'.".$row));
                $posting = new Posting([
                    'company_id' => $company->id,
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $product->inventoryAccount->id,
                    'debit' => request("item_lines.'amount'.".$row)
                ]);
                $posting->save();
                $payableAmount -= request("item_lines.'amount'.".$row) + $inputTax;
                $taxAmount += $inputTax;
            }
        }
        if ($taxAmount > 0) {
            $posting = new Posting([
                'company_id' => $company->id,
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $taxAccount->id,
                'debit' => $taxAmount
            ]);
            $posting->save();
        }
        $posting = new Posting([
            'company_id' => $company->id,
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $payableAccount->id,
            'debit' => $payableAmount,
            'subsidiary_ledger_id' => $payableSubsidiary->id
        ]);
        $posting->save();
    }
    public function destroy(Bill $bill)
    {
        $bill->delete();
        return redirect(route('bills.index'));
    }
}
