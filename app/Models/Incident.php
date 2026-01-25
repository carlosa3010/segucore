<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; // <--- Importante para manejar la zona horaria

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'customer_id',
        'alarm_account_id',
        'alarm_event_id',   // Vinculo con el evento SIA
        'gps_device_id',
        'operator_id',
        'created_by',
        'priority',         // low, medium, high, critical
        'status',           // open, in_progress, monitoring, closed
        'result',           // false_alarm, real_police, etc. (Agregado para el cierre)
        'notes',            // Notas operativas
        'closing_notes',    // Notas de cierre
        'occurred_at',      // Fecha de inicio (UTC)
        'resolved_at'       // Fecha de cierre (UTC)
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    /**
     * --- ACCESSORS DE TIEMPO (ZONA HORARIA -04:00) ---
     * Esto permite ver la hora correcta en Venezuela sin dañar la data UTC
     */

    // 1. Hora de Inicio Local (Para Vistas)
    public function getOccurredAtLocalAttribute()
    {
        return $this->occurred_at 
            ? Carbon::parse($this->occurred_at)->setTimezone('America/Caracas') 
            : null;
    }

    // 2. Hora de Cierre Local (Para Vistas)
    public function getResolvedAtLocalAttribute()
    {
        return $this->resolved_at 
            ? Carbon::parse($this->resolved_at)->setTimezone('America/Caracas') 
            : null;
    }

    /**
     * --- ALIAS DE COMPATIBILIDAD ---
     * Tu vista 'console.blade.php' busca 'started_at'. 
     * Esto redirige esa petición a 'occurred_at' para que no de error.
     */
    public function getStartedAtAttribute()
    {
        return $this->occurred_at;
    }

    public function getStartedAtLocalAttribute()
    {
        return $this->occurred_at_local;
    }

    // --- RELACIONES ---

    // Relación con el Evento de Alarma original
    public function alarmEvent()
    {
        return $this->belongsTo(AlarmEvent::class, 'alarm_event_id');
    }
    
    // Alias 'event' por si alguna vista antigua lo llama así
    public function event()
    {
        return $this->belongsTo(AlarmEvent::class, 'alarm_event_id');
    }

    // Relación con la Cuenta
    public function account()
    {
        return $this->belongsTo(AlarmAccount::class, 'alarm_account_id');
    }

    // Alias 'alarmAccount'
    public function alarmAccount()
    {
        return $this->belongsTo(AlarmAccount::class, 'alarm_account_id');
    }

    // Relación con el Cliente
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Relación con GPS (si aplica)
    public function gpsDevice()
    {
        return $this->belongsTo(GpsDevice::class);
    }

    // Operador que atiende el caso
    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    // Usuario que creó el ticket (si fue manual)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Historial de acciones (Bitácora del incidente)
    public function logs()
    {
        return $this->hasMany(IncidentLog::class)->orderBy('created_at', 'desc');
    }
}