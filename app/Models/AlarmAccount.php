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
        'device_model',     
        
        // Notas
        'notes',
        'permanent_notes',
        'temporary_notes',
        'temporary_notes_until',
        'test_mode_until',

        // CAMPOS DE MONITOREO (ESTOS FALTABAN)
        'is_armed',
        'last_checkin_at',
        'last_signal_at'
    ];

    protected $casts = [
        'is_armed' => 'boolean',
        'last_checkin_at' => 'datetime',
        'last_signal_at' => 'datetime',
        'temporary_notes_until' => 'datetime',
        'test_mode_until' => 'datetime',
    ];

    // Relaciones
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function partitions()
    {
        return $this->hasMany(AlarmPartition::class);
    }

    public function zones()
    {
        return $this->hasMany(AlarmZone::class);
    }

    public function panelUsers()
    {
        return $this->hasMany(PanelUser::class);
    }

    public function schedules()
    {
        return $this->hasMany(AccountSchedule::class);
    }

    public function logs()
    {
        return $this->hasMany(AccountLog::class)->orderBy('created_at', 'desc');
    }
}