<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TraccarDevice extends Model
{
    protected $connection = 'traccar'; 
    protected $table = 'tc_devices';
    public $timestamps = false;

    // --- AGREGAR ESTA RELACIÓN ---
    // Un dispositivo tiene una "última posición" referenciada por positionid
    public function position()
    {
        return $this->hasOne(TraccarPosition::class, 'id', 'positionid');
    }
}