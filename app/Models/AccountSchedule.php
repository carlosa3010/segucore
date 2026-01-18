<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'alarm_account_id',
        'type',         // weekly, temporary, holiday
        'day_of_week',  // 0-6 (0=Domingo) o null para temporales
        'open_time',    // Hora apertura
        'close_time',   // Hora cierre
        'reason',       // Descripción (Ej: Inventario)
        'valid_until'   // Fecha caducidad para temporales
    ];

    protected $casts = [
        'valid_until' => 'date', // Para que Carbon maneje la fecha automáticamente
    ];

    public function account()
    {
        return $this->belongsTo(AlarmAccount::class, 'alarm_account_id');
    }
}