<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubsidiaryLedger extends Model
{
    protected $guarded = [];
    public function path()
    {
        return route('subsidiary_ledgers.show', $this);
    }
    public function posting()
    {
        return $this->belongsTo(Posting::class);
    }
}
