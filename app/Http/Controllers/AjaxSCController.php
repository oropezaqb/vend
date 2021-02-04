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
use App\Jobs\Create\SupplierCredit;
use App\Bill;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */

class AjaxSCController extends Controller
{
    public function getDocument(Request $request)
    {
        $company = \Auth::user()->currentCompany->company;
        $purchasableDoc = $request->input('purchasable_doc');
        $docNumber = $request->input('doc_number');
        $document = null;
        switch ($purchasableDoc) {
            case 'Bill':
                $document = Bill::where('company_id', $company->id)->where('bill_number', $docNumber)->first();
                break;
            case 'Cheque':
                $document = Cheque::where('company_id', $company->id)->where('number', $docNumber)->first();
                break;
            default:
                $document = null;
        }
        if (is_null($document)) {
            return response()->json(array('document' => null, 'suppliername' => null,
                'clines' => null, 'ilines' => null, 'accounttitles' => null,
                'productnames' => null), 200);
        }
        $supplier = $document->supplier;
        $accountTitles = array();
        foreach ($document->categoryLines as $documentCLine) {
            $accountTitles[] = array($documentCLine->account->title);
        }
        $productNames = array();
        foreach ($document->itemLines as $documentILine) {
            $productNames[] = array($documentILine->product->name);
        }
        return response()->json(array('document'=> $document, 'suppliername' => $supplier->name,
            'clines' => $document->categoryLines, 'ilines' => $document->itemLines, 'accounttitles' => $accountTitles, 'productnames' => $productNames), 200);
    }
}
