<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlarmAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'account_number',
        'branch_name',
        'installation_address',
        'latitude',
        'longitude',
        'service_status',
        'test_mode_until',
        'device_model',      // Nuevo
        'notes',
        'permanent_notes',   // Nuevo
        'temporary_notes',   // Nuevo
        'temporary_notes_until' // Nuevo
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function zones()
    {
        return $this->hasMany(AlarmZone::class);
    }

    // ESTA ES LA FUNCIÓN QUE FALTABA Y CAUSABA EL ERROR
    public function partitions()
    {
        return $this->hasMany(AlarmPartition::class);
    }

    public function events()
    {
        return $this->hasMany(AlarmEvent::class, 'account_number', 'account_number');
    }
}