<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guard extends Model
{
    protected $fillable = ['user_id', 'full_name', 'badge_number', 'phone', 'current_patrol_id', 'on_duty'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function patrol()
    {
        return $this->belongsTo(Patrol::class, 'current_patrol_id');
    }
}