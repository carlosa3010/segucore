<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlarmEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_number', // Nombre correcto según tu migración
        'event_code',
        'event_type',
        'zone',
        'partition',
        'ip_address',
        'raw_data',
        'received_at',
        'processed'
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'processed' => 'boolean',
    ];

    /**
     * Relación: Un evento pertenece a una Cuenta de Alarma.
     * Vinculamos la columna 'account_number' (evento) con 'account_number' (cuenta).
     */
    public function account()
    {
        // belongsTo(Modelo, Foreign Key en esta tabla, Owner Key en la otra tabla)
        return $this->belongsTo(AlarmAccount::class, 'account_number', 'account_number');
    }

    /**
     * Helper: Obtener descripción del código SIA (Ej: "Robo", "Fuego").
     * Uso: $event->sia_code_description
     */
    public function getSiaCodeDescriptionAttribute()
    {
        $sia = SiaCode::where('code', $this->event_code)->first();
        return $sia ? $sia->description : 'Evento Desconocido';
    }
}