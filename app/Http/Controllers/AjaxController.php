<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class AjaxController extends Controller
{
   public function getInvoices($customerId) {
      $customer = Customer::find($customerId);
      $invoices = Invoice::where('customer_id', $customer);
      $unpaidInvoicesIds = array();
      foreach ($invoices as $invoice)
      {
          $amountReceivable = $invoice->itemLines->sum('amount') + $invoice->itemLines->sum('output_tax');
          $amountPaid = \DB::table('received_payment_lines')->where('invoice_id', $invoice->id)->sum('amount');
          if($amountReceivable > $amountPaid){
              $unpaidInvoicesIds[] = $invoice->id;
          }
      }
      return response()->json(array('invoices'=> $$unpaidInvoicesIds), 200);
   }
}
