<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name', 
        'last_name',        // Agregado
        'national_id', 
        'email',
        'phone_1',          // Corregido
        'phone_2',          // Agregado
        'address',          // Corregido
        'city',             // Agregado
        'monitoring_password', 
        'duress_password',
        'is_active',
        'notes'
    ];

    // Helper para obtener nombre completo en las vistas
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Relaciones (mantener las que ya tenías si las hay)
    public function accounts() { return $this->hasMany(AlarmAccount::class); }
    public function contacts() { return $this->hasMany(CustomerContact::class); }
    public function gpsDevices() { return $this->hasMany(GpsDevice::class); }
}
