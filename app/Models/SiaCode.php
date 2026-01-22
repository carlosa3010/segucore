<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiaCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',                         // Ej: BA
        'name',                         // Ej: Alarma de Robo
        'description',                  // Descripción breve
        'priority',                     // 0=Log, 1=Info, 2=Alerta, 3=Crítico
        'category',                     // alarm, status, test, power
        
        // --- NUEVOS CAMPOS (Configuración Avanzada) ---
        'procedure_instructions',       // Texto: Pasos a seguir por el operador
        'requires_schedule_check',      // Bool: ¿Validar horario de apertura/cierre?
        'schedule_grace_minutes',       // Int: Minutos de tolerancia
        'schedule_violation_action'     // Enum: 'none', 'warning', 'critical_alert'
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