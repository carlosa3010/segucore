<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlarmEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'alarm_account_id', // <--- ESTE ES EL CAMPO CORRECTO
        'event_code',
        'code',
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

    // Relación correcta para los Reportes
    public function account()
    {
        return $this->belongsTo(AlarmAccount::class, 'alarm_account_id');
    }
}