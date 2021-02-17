<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryQtyAdj extends Model
{
    protected $guarded = [];
    public function path()
    {
        return route('inventory_qty_adjs.show', $this);
    }
    public function lines()
    {
        return $this->hasMany(InventoryQtyAdjLine::class);
    }
    public function sales()
    {
        return $this->morphMany('App\Sale', 'salable');
    }
    public function purchases()
    {
        return $this->morphMany('App\Purchase', 'purchasable');
    }
    public function journalEntry()
    {
        return $this->morphOne('App\JournalEntry', 'journalizable');
    }
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
    public function transaction()
    {
        return $this->morphOne('App\Transaction', 'transactable');
    }
    public function delete()
    {
        $res=parent::delete();
        if ($res==true) {
            $sales = $this->sales;
            foreach ($sales as $sale) {
                $sale->delete();
            }
            $purchases = $this->purchases;
            foreach ($purchases as $purchase) {
                $purchase->delete();
            }
            if (!is_null($this->journalEntry)) {
                $this->journalEntry->delete();
            }
            if (!is_null($this->transaction)) {
                $this->transaction->delete();
            }
        }
    }
}
