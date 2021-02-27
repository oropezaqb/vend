<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LineItem extends Model
{
    protected $guarded = [];
    public function path()
    {
        return route('line_items.show', $this);
    }
}
