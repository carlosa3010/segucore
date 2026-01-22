<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GpsDevice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'name',
        'imei', // <--- CAMBIO AQUI
        'phone_number',
        'sim_card_id',
        'device_model',
        'plate_number',
        'vehicle_type',
        'driver_name',
        'installation_date',
        'subscription_status'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Relación con Traccar
    public function traccarData()
    {
        // Enlace: 'imei' local <---> 'uniqueid' remoto
        return $this->setConnection('traccar')->hasOne(TraccarDevice::class, 'uniqueid', 'imei');
    }
}