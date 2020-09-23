<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    public function path()
    {
        return route('invoices.show', $this);
    }
}
