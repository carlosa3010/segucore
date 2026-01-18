<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name', 'last_name', 'national_id', 'email', 
        'phone_1', 'phone_2', 'address', 'city', 
        'monitoring_password', 'duress_password', 'notes', 'is_active'
    ];

    // Relación: Un cliente tiene muchas cuentas de alarma (Paneles)
    public function accounts()
    {
        return $this->hasMany(AlarmAccount::class);
    }

    // Relación: Un cliente tiene muchos GPS
    public function gpsDevices()
    {
        return $this->hasMany(GpsDevice::class);
    }

    // Relación: Lista de contactos de emergencia
    public function contacts()
    {
        return $this->hasMany(CustomerContact::class)->orderBy('priority');
    }

    // Relación: Facturas
    public function invoices()
    {
        return $this->hasMany(Invoice::class)->latest();
    }
    
    // Helper: Nombre completo
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
