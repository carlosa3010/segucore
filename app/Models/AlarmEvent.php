<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlarmEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'alarm_account_id', // <--- CORREGIDO (Antes era account_number)
        'event_code',
        'code',             // Guardamos ambos por compatibilidad
        'description',
        'zone',
        'partition',
        'raw_data',
        'received_at',
        'processed'
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'processed' => 'boolean',
    ];

    // Relación con la Cuenta (Vital para que funcione el reporte)
    public function account()
    {
        return $this->belongsTo(AlarmAccount::class, 'alarm_account_id');
    }

    // ESTA ES LA RELACIÓN QUE FALTABA Y DABA EL ERROR
    public function siaCode()
    {
        // Relaciona el campo 'event_code' de esta tabla con 'code' de la tabla sia_codes
        return $this->belongsTo(SiaCode::class, 'event_code', 'code');
    }
}