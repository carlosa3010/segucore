<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlarmEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'alarm_account_id', // CRÍTICO: Usamos el ID, no el número de cuenta
        'event_code',       // CRÍTICO: Coincide con tu base de datos
        'code',             // Campo adicional estándar
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

    // Relación con la cuenta
    public function account()
    {
        return $this->belongsTo(AlarmAccount::class, 'alarm_account_id');
    }

    // Relación con el código SIA
    public function siaCode()
    {
        return $this->belongsTo(SiaCode::class, 'event_code', 'code');
    }
}