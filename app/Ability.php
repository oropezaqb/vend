<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ability extends Model
{
    protected $guarded = [];
    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }
    public function path()
    {
        return route('abilities.show', $this);
    }
}
