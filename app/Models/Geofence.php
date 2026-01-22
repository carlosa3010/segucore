<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Geofence extends Model
{
    protected $fillable = [
        'name', 
        'description', 
        'area', 
        'traccar_geofence_id'
    ];

    /**
     * Relación: Una geocerca puede estar asignada a muchos dispositivos.
     */
    public function devices()
    {
        return $this->belongsToMany(GpsDevice::class, 'geofence_gps_device');
    }
}