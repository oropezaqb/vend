<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $guarded = [];
    public function path()
    {
        return route('suppliers.show', $this);
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
