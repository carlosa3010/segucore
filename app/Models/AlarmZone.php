<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlarmZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'alarm_account_id',
        'partition_id', // <--- Importante para el error que te da
        'zone_number',
        'name',
        'type',
        'sensor_type'
    ];

    /**
     * Relación con la Cuenta de Alarma.
     */
    public function account()
    {
        return $this->belongsTo(AlarmAccount::class, 'alarm_account_id');
    }

    /**
     * Relación con la Partición (Área).
     * Esta es la función que te faltaba y causaba el error.
     */
    public function partition()
    {
        return $this->belongsTo(AlarmPartition::class);
    }
}