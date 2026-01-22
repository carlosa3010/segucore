<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatrolRound extends Model
{
    protected $fillable = ['name', 'description', 'interval_minutes', 'is_active'];

    public function checkpoints()
    {
        return $this->belongsToMany(Geofence::class, 'patrol_round_checkpoints')
                    ->withPivot('order_index')
                    ->orderBy('pivot_order_index');
    }
}