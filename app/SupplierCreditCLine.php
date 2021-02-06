<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SupplierCreditCLine extends Model
{
    protected $guarded = [];
    protected $table = 'supplier_credit_clines';
    public function purchasable()
    {
        return $this->morphTo();
    }
}
