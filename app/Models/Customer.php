<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'first_name', 'last_name', 'business_name', 'dni_cif',
        'email', 'phone_1', 'phone_2', 'address', 'city', 'postal_code',
        'country', 'notes', 'is_active', 'type'
    ];

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => !empty($attributes['business_name']) 
                ? $attributes['business_name'] 
                : trim(($attributes['first_name'] ?? '') . ' ' . ($attributes['last_name'] ?? ''))
        );
    }

    // Relaciones
    public function accounts() { return $this->hasMany(AlarmAccount::class); }
    public function gpsDevices() { return $this->hasMany(GpsDevice::class); }
    public function invoices() { return $this->hasMany(Invoice::class); }
    public function users() { return $this->hasMany(User::class); }
    public function contacts() { return $this->hasMany(CustomerContact::class); }
}