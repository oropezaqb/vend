<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    protected $guarded = [];
    public function returnable_sale()
    {
        return $this->morphTo();
    }
}
