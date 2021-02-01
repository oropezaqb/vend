<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded = [];
    public function transactable()
    {
        return $this->morphTo(__FUNCTION__, 'transactable_type', 'transactable_id');
    }
}
