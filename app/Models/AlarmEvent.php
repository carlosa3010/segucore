<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlarmEvent extends Model
{
    use HasFactory;

    // ESTO ES LO QUE FALTA: Permisos de escritura
    protected $fillable = [
        'account_number',
        'event_code',
        'event_type',
        'zone',
        'partition',
        'ip_address',
        'raw_data',
        'received_at',
        'processed'
    ];

    // Convertir fechas automáticamente
    protected $casts = [
        'received_at' => 'datetime',
        'processed' => 'boolean',
    ];
}