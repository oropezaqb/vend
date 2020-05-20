<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CurrentCompany extends Model
{
    protected $guarded = [];
    protected $table = 'current_company';
    public function path()
    {
        return route('current_company.show', $this);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
