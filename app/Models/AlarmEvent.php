<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlarmEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_number',
        'event_code',
        'event_type',
        'zone',
        'partition',
        'ip_address',
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
     */
    public function account()
    {
        return $this->belongsTo(AlarmAccount::class, 'account_number', 'account_number');
    }

    /**
     * Relación: Un evento corresponde a una definición de código SIA.
     */
    public function siaCode()
    {
        return $this->belongsTo(SiaCode::class, 'event_code', 'code');
    }

    /**
     * Relación: Un evento puede haber generado UN Incidente de atención.
     * ESTA ES LA RELACIÓN QUE FALTABA
     */
    public function incident()
    {
        return $this->hasOne(Incident::class);
    }

    /**
     * Helper opcional para descripción
     */
    public function getSiaCodeDescriptionAttribute()
    {
        return $this->siaCode ? $this->siaCode->description : 'Evento Desconocido';
    }
}