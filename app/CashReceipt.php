<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CashReceipt extends Model
{
    protected $guarded = [];
    public function path()
    {
        return route('cash_receipts.show', $this);
    }
    public function lines()
    {
        return $this->hasMany(CashReceiptLine::class);
    }
    public function subsidiaryLedger()
    {
        return $this->belongsTo(SubsidiaryLedger::class, 'subsidiary_ledger_id');
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
