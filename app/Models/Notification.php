<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'notifiable_id');
    }
}
