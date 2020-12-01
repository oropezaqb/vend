<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Customer;
use App\Invoice;

class AjaxController extends Controller
{
   public function store(Request $request) {
      $id = $request->input('customer_id');
      $customer = Customer::find($id);
      $invoices = Invoice::where('customer_id', $customer->id)->get();
      $unpaidInvoicesIds = array();
      foreach ($invoices as $invoice)
      {
          $amountReceivable = $invoice->itemLines->sum('amount') + $invoice->itemLines->sum('output_tax');
          $amountPaid = \DB::table('received_payment_lines')->where('invoice_id', $invoice->id)->sum('amount');
          if($amountReceivable > $amountPaid){
             $unpaidInvoicesIds[] = $invoice->id;
          }
      }
      return response()->json(array('invoices'=> $unpaidInvoicesIds), 200);
   }
}
