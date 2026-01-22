<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patrol extends Model
{
    protected $fillable = ['name', 'vehicle_type', 'plate_number', 'gps_device_id', 'is_active'];

    public function gpsDevice()
    {
        return $this->belongsTo(GpsDevice::class);
    }

    public function guards()
    {
        return $this->hasMany(Guard::class, 'current_patrol_id');
    }
}