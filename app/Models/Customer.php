<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'national_id', 'first_name', 'last_name', 'business_name',
        'email', 'phone_1', 'phone_2', 'address_billing', 'city',
        'monitoring_password', 'duress_password', 'is_active', 'notes'
    ];

    // Accesor inteligente: Devuelve Nombre de Empresa o Nombre Persona
    public function getFullNameAttribute()
    {
        if ($this->type === 'company') {
            return $this->business_name; // Ej: "Panadería C.A."
        }
        return "{$this->first_name} {$this->last_name}"; // Ej: "Juan Pérez"
    }
    
    // Relaciones... (mantener las existentes)
    public function accounts() { return $this->hasMany(AlarmAccount::class); }
    public function contacts() { return $this->hasMany(CustomerContact::class); }
    public function gpsDevices() { return $this->hasMany(GpsDevice::class); }
    public function invoices() { return $this->hasMany(Invoice::class); }
}