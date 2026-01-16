<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TraccarDevice extends Model
{
    // ESTA ES LA MAGIA: Usar la conexión de lectura de Traccar
    protected $connection = 'traccar'; 

    // La tabla real en la BD de Traccar
    protected $table = 'tc_devices';

    // Traccar no usa los timestamps de Laravel (created_at, updated_at) por defecto
    public $timestamps = false;
}
