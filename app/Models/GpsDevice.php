<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GpsDevice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'driver_id', // <--- Asegúrate de que esto esté en fillable
        'name',
        'imei',
        'device_model',
        'phone_number',
        'plate_number',
        'vehicle_type',
        'subscription_status',
        'speed_limit',
        'odometer'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Relación con Conductor
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    // Relación con Geocercas (Muchos a Muchos)
    public function geofences()
    {
        return $this->belongsToMany(Geofence::class, 'geofence_gps_device');
    }
}