<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TraccarPosition extends Model
{
    protected $connection = 'traccar';
    protected $table = 'tc_positions';
    public $timestamps = false;

    // Traccar guarda datos extra (Batería, Ignición, Movimiento) en un JSON string llamado 'attributes'
    protected $casts = [
        'servertime' => 'datetime',
        'devicetime' => 'datetime',
        'fixtime' => 'datetime',
        'attributes' => 'array', // Esto nos permite leer $position->attributes['batteryLevel']
    ];

    public function device()
    {
        return $this->belongsTo(TraccarDevice::class, 'deviceid', 'id');
    }
}