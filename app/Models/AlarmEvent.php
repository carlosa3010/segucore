<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlarmEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'account', // El número de abonado (ej: 1234)
        'event_code', // El código SIA (ej: BA)
        'zone',
        'raw_data',
        'processed'
    ];

    /**
     * Relación: Un evento pertenece a una Cuenta de Alarma.
     * Vinculamos la columna 'account' (evento) con 'account_number' (cuenta).
     */
    public function account()
    {
        return $this->belongsTo(AlarmAccount::class, 'account', 'account_number');
    }
    
    // Helper para obtener descripción del código SIA
    public function getSiaCodeDescriptionAttribute()
    {
        // Intenta buscar el código en la tabla cacheada o BD
        $sia = SiaCode::where('code', $this->event_code)->first();
        return $sia ? $sia->description : 'Evento Desconocido';
    }
}