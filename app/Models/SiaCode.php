<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiaCode extends Model
{
    protected $fillable = [
        'code',         // Ej: BA, RP, OP
        'name',         // Ej: Alarma de Robo, Test Automático
        'description',  // Instrucciones para el operador
        'priority',     // 0=Log (Ignorar), 1=Info (Monitor), 2=Alarma (Sonar), 3=Pánico
        'category'      // 'alarm', 'status', 'test', 'access'
    ];
}