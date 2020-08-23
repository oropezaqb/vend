<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Cmgmyr\Messenger\Traits\Messagable;

class User extends Authenticatable
{
    use Notifiable;
    use Messagable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'is_active', 'photo_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function companies()
    {
        return $this->belongsToMany(Company::class)->withTimestamps();
    }
    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }
    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = Role::whereName($role)->firstOrFail();
        }
        $this->roles()->sync($role, false);
    }
    public function abilities()
    {
        return $this->roles->map->abilities->flatten()->pluck('name')->unique();
    }
    public function applications()
    {
        return $this->hasMany(Application::class);
    }
    public function currentCompany()
    {
        return $this->hasOne(CurrentCompany::class);
    }
    public function photo()
    {
        return $this->belongsTo('App\Photo');
    }
    public function isAdmin()
    {
        if ($this->role->name == "admin" && $this->is_active == 1) {
            return true;
        }
        return false;
    }
    
    public function isBowner()
    {
        if ($this->role->name == "business" && $this->is_active == 1) {
            return true;
        }
        return false;
    }

    public function isManager()
    {
        if ($this->role->name == "manager" && $this->is_active == 1) {
            return true;
        }
        return false;
    }

    public function isEmployee()
    {
        if ($this->role->name == "employee" && $this->is_active == 1) {
            return true;
        }
        return false;
    }
}
