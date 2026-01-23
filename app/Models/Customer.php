<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute; // Importante para Laravel 9/10/11

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'first_name',
        'last_name',
        'business_name',
        'dni_cif',
        'email',
        'phone_1',
        'phone_2',
        'address',
        'city',
        'postal_code',
        'country',
        'notes',
        'is_active',
        'type' // 'person' o 'company'
    ];

    /**
     * ACCESSOR: Nombre Virtual
     * Permite usar $customer->name en cualquier vista
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                // Si tiene Razón Social (Empresa), mostrar eso.
                if (!empty($attributes['business_name'])) {
                    return $attributes['business_name'];
                }
                
                // Si no, mostrar Nombre + Apellido
                return trim(($attributes['first_name'] ?? '') . ' ' . ($attributes['last_name'] ?? ''));
            }
        );
    }

    // --- Relaciones ---

    public function contacts()
    {
        return $this->hasMany(CustomerContact::class);
    }

    public function accounts()
    {
        return $this->hasMany(AlarmAccount::class);
    }
    
    public function users()
    {
        return $this->hasMany(User::class);
    }
    
    public function gpsDevices()
    {
        return $this->hasMany(GpsDevice::class);
    }
}