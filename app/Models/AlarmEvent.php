<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; // <--- IMPORTANTE: Necesario para la conversión de hora

class AlarmEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'alarm_account_id', // Relación con la cuenta
        'event_code',       // Código SIA (Ej: UA, BA)
        'code',             // Duplicado por compatibilidad
        'description',      // Descripción del evento
        'zone',             // Número de zona o usuario
        'partition',        // Partición (Ej: 01)
        'raw_data',         // Trama cruda recibida
        'received_at',      // Fecha en UTC (Base de datos)
        'processed'         // Si ya fue procesado/atendido
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'processed' => 'boolean',
    ];

    /**
     * Accessor para obtener la fecha en Hora Local (Venezuela)
     * Uso: $event->received_at_local
     */
    public function getReceivedAtLocalAttribute()
    {
        return $this->received_at 
            ? Carbon::parse($this->received_at)->setTimezone('America/Caracas') 
            : null;
    }

    // Relación con la Cuenta
    public function account()
    {
        return $this->belongsTo(AlarmAccount::class, 'alarm_account_id');
    }

    // Relación con la tabla de códigos SIA
    public function siaCode()
    {
        // Relaciona el campo 'event_code' de esta tabla con 'code' de la tabla sia_codes
        return $this->belongsTo(SiaCode::class, 'event_code', 'code');
    }

    public function incident()
    {
        return $this->hasOne(Incident::class, 'alarm_event_id');
    }
}