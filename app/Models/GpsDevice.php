<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GpsDevice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'imei',
        'customer_id',
        'driver_id',
        'name',
        'plate_number',
        'model',
        'sim_card_number',
        'speed_limit',
        'status',
        'last_latitude',
        'last_longitude',
        'speed',
        'battery_level',
        'settings',
        'is_active'
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'last_latitude' => 'decimal:7',
        'last_longitude' => 'decimal:7',
    ];

    // 1. Relación con Cliente (Dueño)
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // 2. Relación con Conductor (Asignado)
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    // 3. Relación con Geocercas (Esta era la que faltaba y daba el error)
    public function geofences()
    {
        return $this->hasMany(Geofence::class);
    }
}
