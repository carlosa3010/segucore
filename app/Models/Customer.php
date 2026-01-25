<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'national_id',       // CORREGIDO: Antes era dni_cif
        'first_name',
        'last_name',
        'business_name',
        'email',
        'phone_1',
        'phone_2',
        'address',
        'address_billing',   // AGREGADO: Requerido por el controlador
        'city',
        'monitoring_password',
        'duress_password',   // AGREGADO: Requerido por el controlador
        'notes',
        'status',            // AGREGADO: Requerido por las vistas
        'is_active'
    ];

    // Accessor para obtener el nombre completo o razón social
    protected function fullName(): Attribute
{
    return Attribute::make(
        get: fn ($value, $attributes) => !empty($attributes['business_name']) 
            ? $attributes['business_name'] 
            : trim(($attributes['first_name'] ?? '') . ' ' . ($attributes['last_name'] ?? ''))
    );
}

    // Relaciones
    public function users() { return $this->hasMany(User::class); }
    public function accounts() { return $this->hasMany(AlarmAccount::class); }
    public function gpsDevices() { return $this->hasMany(GpsDevice::class); }
    public function drivers() { return $this->hasMany(Driver::class); }
    public function contacts() { return $this->hasMany(CustomerContact::class); }
    public function invoices() { return $this->hasMany(Invoice::class); }
    public function servicePlan()
{
    return $this->belongsTo(ServicePlan::class);
}

public function invoices()
{
    return $this->hasMany(Invoice::class);
}

}