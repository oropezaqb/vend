<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SupplierCredit extends Model
{
    protected $guarded = [];
    public function path()
    {
        return route('suppliercredit.show', $this);
    }
    public function clines()
    {
        return $this->hasMany(CreditNoteCLine::class);
    }
    public function ilines()
    {
        return $this->hasMany(CreditNoteILine::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
    public function purchaseReturns()
    {
        return $this->morphMany('App\PurchaseReturn', 'returnablepurc');
    }
    public function journalEntry()
    {
        return $this->morphOne('App\JournalEntry', 'journalizable');
    }
    public function purchasable()
    {
        return $this->morphTo();
    }
    public function delete()
    {
        $res=parent::delete();
        if ($res==true) {
            $purchaseReturns = $this->purchaseReturns;
            foreach ($purchaseReturns as $purchaseReturn) {
                $purchaseReturn->delete();
            }
            if (!is_null($this->journalEntry)) {
                $this->journalEntry->delete();
            }
        }
    }
}
