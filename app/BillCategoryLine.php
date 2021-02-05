<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BillCategoryLine extends Model
{
    protected $guarded = [];
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
