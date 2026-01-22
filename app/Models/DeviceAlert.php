<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceAlert extends Model
{
    protected $fillable = ['gps_device_id', 'type', 'message', 'read_at', 'data'];
    
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(GpsDevice::class, 'gps_device_id');
    }
}