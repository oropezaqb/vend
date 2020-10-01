<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $guarded = [];
    public function path()
    {
        return route('invoices.show', $this);
    }
    public function itemLines()
    {
        return $this->hasMany(InvoiceItemLine::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function sales()
    {
        return $this->morphMany('App\Sale', 'salable');
    }
    public function journalEntry()
    {
        return $this->morphOne('App\JournalEntry', 'journalizable');
    }
    public function transaction()
    {
        return $this->morphOne('App\Transaction', 'transactable');
    }
    public function delete()
    {
        $res=parent::delete();
        if ($res==true) {
            $relations = $this->sales;
            foreach ($relations as $relation) {
                $relation->delete();
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
