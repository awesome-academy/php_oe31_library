<?php

namespace App\Models;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Rate;
use App\Models\Request;
use App\Models\Role;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Notification;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'address',
        'phone',
        'role_id',
        'times',
        'status',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function rates()
    {
        return $this->hasMany(Rate::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function notification()
    {
        return $this->hasMany(Notification::class, 'id', 'notifiable_id');   
    }

    public function hasPermission(Permission $permission)
    {
        $check = optional(optional($this->role)->permissions)->contains($permission);

        return $check;
    }
}
