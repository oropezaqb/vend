<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $guarded = [];
    public function path()
    {
        return route('accounts.show', $this);
    }
    public function posting()
    {
        return $this->belongsTo(Posting::class);
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
