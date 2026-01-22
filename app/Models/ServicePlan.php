<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePlan extends Model
{
    protected $fillable = [
        'name', 
        'price', 
        'currency', 
        'billing_cycle_days',
        'description' // Sugiero agregar este campo en una futura migración si no existe
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'billing_cycle_days' => 'integer',
    ];

    // Relación: Un plan tiene muchas cuentas suscritas
    public function accounts()
    {
        return $this->hasMany(AlarmAccount::class);
    }
    
    // Helper para mostrar precio formateado
    public function getFormattedPriceAttribute()
    {
        return $this->currency . ' ' . number_format($this->price, 2);
    }
}