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
        'processed_at' // <--- Faltaba este campo vital
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'processed_at' => 'datetime', // <--- Importante para operaciones de fecha
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
     * Esto soluciona el error "undefined relationship [siaCode]".
     */
    public function siaCode()
    {
        // belongsTo(Modelo, FK_local, Owner_Key_remota)
        return $this->belongsTo(SiaCode::class, 'event_code', 'code');
    }

    /**
     * Helper opcional para compatibilidad
     */
    public function getSiaCodeDescriptionAttribute()
    {
        return $this->siaCode ? $this->siaCode->description : 'Evento Desconocido';
    }
}