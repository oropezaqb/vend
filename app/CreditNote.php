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
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function journalEntry()
    {
        return $this->morphOne('App\JournalEntry', 'journalizable');
    }
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
    public function delete()
    {
        $res=parent::delete();
        if ($res==true) {
            if (!is_null($this->journalEntry)) {
                $this->journalEntry->delete();
            }
        }
    }
}
