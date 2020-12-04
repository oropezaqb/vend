<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReceivedPayment extends Model
{
    protected $guarded = [];
    public function path()
    {
        return route('received_payments.show', $this);
    }
    public function lines()
    {
        return $this->hasMany(ReceivedPaymentLine::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function journalEntry()
    {
        return $this->morphOne('App\JournalEntry', 'journalizable');
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
