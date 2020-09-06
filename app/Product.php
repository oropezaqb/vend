<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];
    public function path()
    {
        return route('products.show', $this);
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function receivableAccount()
    {
        return $this->belongsTo(Account::class);
    }
    public function inventoryAccount()
    {
        return $this->belongsTo(Account::class);
    }
    public function incomeAccount()
    {
        return $this->belongsTo(Account::class);
    }
    public function expenseAccount()
    {
        return $this->belongsTo(Account::class);
    }
}
