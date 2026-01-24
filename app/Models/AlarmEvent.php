<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlarmEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'alarm_account_id', // <--- CORREGIDO: Antes decía 'account_number'
        'event_code',       // Este debe coincidir con la migración
        'event_type',       // Asegúrate que tu migración tenga este campo o quítalo
        'zone',
        'partition',
        'ip_address',       // Asegúrate que tu migración tenga este campo (o raw_data lo cubra)
        'raw_data',
        'received_at',
        'processed',
        'processed_at'
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
        'processed' => 'boolean',
    ];

    /**
     * Relación: Un evento pertenece a una Cuenta de Alarma.
     * CORREGIDO: Usamos la relación estándar de Laravel (busca alarm_account_id automáticamente)
     */
    public function account()
    {
        return $this->belongsTo(AlarmAccount::class);
    }

    public function siaCode()
    {
        // Asegúrate que en la DB sea 'event_code'
        return $this->belongsTo(SiaCode::class, 'event_code', 'code');
    }

    public function incident()
    {
        return $this->hasOne(Incident::class);
    }

    public function getSiaCodeDescriptionAttribute()
    {
        return $this->siaCode ? $this->siaCode->description : 'Evento Desconocido';
    }
}