<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicePlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price', // Precio Base del plan (ej. Mantenimiento mensual)
        'billing_cycle',
        'features', // Aquí guardaremos las tasas: { "gps_price": 5, "alarm_price": 20 }
        'description',
        'is_active',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    // Helpers para obtener las tasas fácilmente
    public function getGpsPriceAttribute()
    {
        return $this->features['gps_price'] ?? 0;
    }

    public function getAlarmPriceAttribute()
    {
        return $this->features['alarm_price'] ?? 0;
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}