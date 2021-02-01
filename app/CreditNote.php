<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CreditNote extends Model
{
    protected $guarded = [];
    public function path()
    {
        return route('creditnote.show', $this);
    }
    public function lines()
    {
        return $this->hasMany(CreditNoteLine::class);
    }
    public function journalEntry()
    {
        return $this->morphOne('App\JournalEntry', 'journalizable');
    }
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
    public function transaction()
    {
        return $this->morphOne('App\Transaction', 'transactable');
    }
    public function salesReturns()
    {
        return $this->morphMany('App\SalesReturn', 'returnable_sale');
    }
    public function purchases()
    {
        return $this->morphMany('App\Purchase', 'purchasable');
    }
    public function delete()
    {
        $res=parent::delete();
        if ($res==true) {
            $relations = $this->salesReturns;
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
