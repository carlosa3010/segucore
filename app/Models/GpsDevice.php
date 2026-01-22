<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GpsDevice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'driver_id', // <--- IMPORTANTE: Agregado para poder guardar el conductor seleccionado
        'name',
        'imei',
        'phone_number',
        'sim_card_id',
        'device_model',
        'plate_number',
        'vehicle_type',
        'driver_name', // (Legacy) Mantenemos por si acaso, pero usaremos driver_id
        'installation_date',
        'subscription_status',
        'speed_limit',
        'odometer',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    // Relación con Geocercas (Muchos a Muchos) - NUEVO
    public function geofences()
    {
        return $this->belongsToMany(Geofence::class, 'geofence_gps_device');
    }

    // Relación con Traccar
    public function traccarData()
    {
        // Enlace: 'imei' local <---> 'uniqueid' remoto
        return $this->setConnection('traccar')->hasOne(TraccarDevice::class, 'uniqueid', 'imei');
    }
}