<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'alarm_event_id',
        'alarm_account_id', // Obligatorio
        'customer_id',
        'operator_id',
        'status',      // open, in_progress, monitoring, closed
        'result',      // false_alarm, real, test, etc.
        'notes',       // Notas del operador
        'started_at',
        'closed_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Relación: Un incidente nace de un Evento de Alarma específico.
     */
    public function alarmEvent()
    {
        return $this->belongsTo(AlarmEvent::class);
    }

    /**
     * Relación: El incidente pertenece a una Cuenta de Alarma.
     */
    public function account()
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
     * Relación: El incidente fue atendido por un Operador (Usuario).
     */
    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    /**
     * Relación: Un incidente tiene muchos registros en la bitácora.
     * ESTA ES LA QUE FALTABA
     */
    public function logs()
    {
        return $this->hasMany(IncidentLog::class)->orderBy('created_at', 'desc');
    }
}