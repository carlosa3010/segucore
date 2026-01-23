<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GpsDevice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'imei',
        'customer_id',
        'name',
        'model',            // <--- Importante
        'sim_card_number',  // <--- Importante
        'status',
        'last_latitude',
        'last_longitude',
        'speed',
        'battery_level',
        'settings',
        'is_active'
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'last_latitude' => 'decimal:7',
        'last_longitude' => 'decimal:7',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}