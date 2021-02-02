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
use App\Invoice;
use App\Jobs\CreateInvoice;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */

class AjaxCNController extends Controller
{
    public function getInvoice(Request $request)
    {
        $company = \Auth::user()->currentCompany->company;
        $id = $request->input('invoice_number');
        $invoice = Invoice::where('company_id', $company->id)->where('invoice_number', $id)->first();
        if (is_null($invoice)) {
            return response()->json(array('invoice' => null, 'customername' => null,
                'invoicelines' => null, 'productnames' => null), 200);
        }
        $customer = $invoice->customer;
        $productNames = array();
        foreach ($invoice->itemLines as $invoiceLine) {
            $productNames[] = array($invoiceLine->product->name);
        }
        return response()->json(array('invoice'=> $invoice, 'customername' => $customer->name,
            'invoicelines' => $invoice->itemLines, 'productnames' => $productNames), 200);
    }
    public function getAmounts(Request $request)
    {
        $invoiceId = $request->input('invoice_id');
        $productId = $request->input('invoice_line_id');
        $quantity = $request->input('quantity_returned');
        $creditNoteId = $request->input('credit_note_id');
        $createCreditNote = new CreateCreditNote();
        $amounts = $createCreditNote->determineAmounts($invoiceId, $productId, $quantity, $creditNoteId);
        if (is_null($amounts)) {
            return response()->json(array('amounts' => null), 200);
        }
        return response()->json(array('amounts' => $amounts), 200);
    }
}
