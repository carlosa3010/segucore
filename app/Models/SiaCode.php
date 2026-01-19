<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiaCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',         // Ej: BA
        'name',         // Ej: Alarma de Robo
        'description',  // Instrucciones para el operador
        'priority',     // 0=Log, 1=Info, 2=Alerta, 3=Crítico
        'category'      // alarm, status, test, power
    ];
}