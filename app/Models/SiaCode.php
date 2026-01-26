<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiaCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'priority',
        'category',
        'color_hex',    // <--- FALTABA ESTE
        'sound_alert',  // <--- FALTABA ESTE
        
        // Configuración Avanzada
        'procedure_instructions',
        'requires_schedule_check',
        'schedule_grace_minutes',
        'schedule_violation_action'
    ];

    /**
     * Casts de atributos para asegurar tipos de datos correctos.
     */
    protected $casts = [
        'requires_schedule_check' => 'boolean',
        'schedule_grace_minutes' => 'integer',
        'priority' => 'integer',
    ];
}