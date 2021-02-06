<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    protected $guarded = [];
    protected $table = 'purc_returns';
    public function returnablepurc()
    {
        return $this->morphTo();
    }
    public function purchasable()
    {
        return $this->morphTo();
    }
}
