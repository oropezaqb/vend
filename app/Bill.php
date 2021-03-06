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
    public function purchases()
    {
        return $this->morphMany('App\Purchase', 'purchasable');
    }
    public function journalEntry()
    {
        return $this->morphOne('App\JournalEntry', 'journalizable');
    }
    public function supplierCredits()
    {
        return $this->morphMany('App\SupplierCredit', 'purchasable');
    }
    public function supplierCreditCLine()
    {
        return $this->morphMany('App\SupplierCreditCLine', 'purchasable');
    }
    public function supplierCreditILine()
    {
        return $this->morphMany('App\SupplierCreditILine', 'purchasable');
    }
    public function purchaseReturns()
    {
        return $this->morphMany('App\PurchaseReturn', 'purchasable');
    }
    public function delete()
    {
        $res = parent::delete();
        if ($res==true) {
            $relations = $this->purchases;
            foreach ($relations as $relation) {
                $relation->delete();
            }
            if (!is_null($this->journalEntry)) {
                $this->journalEntry->delete();
            }
        }
    }
}
