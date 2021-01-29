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
