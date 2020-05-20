<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $guarded = [];
    public function path()
    {
        return route('companies.show', $this);
    }
    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
    public function employ($user)
    {
        if (is_string($user)) {
            $user = User::whereName($user)->firstOrFail();
        }
        $this->users()->sync($user, false);
    }
    public function applications()
    {
        return $this->hasMany(Application::class);
    }
    public function currentCompany()
    {
        return $this->hasMany(CurrentCompany::class);
    }
}
