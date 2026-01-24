<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'customer_id',
        'alarm_account_id',
        'alarm_event_id',   // <--- Crucial para vincular con el evento
        'gps_device_id',
        'operator_id',
        'created_by',
        'priority',         // low, medium, high, critical
        'status',           // open, in_progress, monitoring, closed
        'occurred_at',      // Coincide con la migración
        'resolved_at'       // Coincide con la migración
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    /**
     * Relación: Un incidente nace de un Evento de Alarma específico.
     */
    public function alarmEvent()
    {
        return $this->belongsTo(AlarmEvent::class, 'alarm_event_id');
    }
    
    // Alias por si alguna vista usa 'event'
    public function event()
    {
        return $this->belongsTo(AlarmEvent::class, 'alarm_event_id');
    }

    /**
     * Relación: El incidente pertenece a una Cuenta de Alarma.
     */
    public function account()
    {
        return $this->belongsTo(AlarmAccount::class, 'alarm_account_id');
    }
    
    // Alias para consistencia
    public function alarmAccount()
    {
        return $this->belongsTo(AlarmAccount::class, 'alarm_account_id');
    }

    /**
     * Relación: El incidente pertenece a un Cliente.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relación: El incidente está asociado a un GPS (Opcional).
     */
    public function gpsDevice()
    {
        return $this->belongsTo(GpsDevice::class);
    }

    /**
     * Relación: El incidente fue atendido por un Operador.
     */
    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    /**
     * Relación: Usuario que creó el incidente manualmente.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relación: Un incidente tiene muchos registros en la bitácora.
     */
    public function logs()
    {
        return $this->hasMany(IncidentLog::class)->orderBy('created_at', 'desc');
    }
}