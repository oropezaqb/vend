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
use App\Bill;

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */

class AjaxInvQtyAdjController extends Controller
{
    public function getQuantities(Request $request)
    {
        $adjDate = $request->input('date');
        $productId = $request->input('product_id');
        $quantities = $this->determineQuantities(
            $adjDate,
            $productId
        );
        if (is_null($quantities)) {
            return response()->json(array('quantities' => null), 200);
        }
        return response()->json(array('quantities' => $quantities), 200);
    }
    public function determineQuantities($adjDate, $productId)
    {
        $quantities = array();
        $quantityPurchased = \DB::table('purchases')->where('product_id', $productId)->whereDate('date', '<=', $adjDate)->sum('quantity');
        $quantitySold = \DB::table('sales')->where('product_id', $productId)->whereDate('date', '<=', $adjDate)->sum('quantity');
        $quantities['qty_on_hand'] = $quantityPurchased - $quantitySold;
        return $quantities;
    }
}