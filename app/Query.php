<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Query extends Model
{
    protected $guarded = [];
    public function path()
    {
        return route('queries.show', $this);
    }
}
