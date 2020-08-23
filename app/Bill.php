<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $guarded = [];
    public function path()
    {
        return route('bills.show', $this);
    }
    public function categoryLines()
    {
        return $this->hasMany(BillCategoryLine::class);
    }
    public function itemLines()
    {
        return $this->hasMany(BillItemLine::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
