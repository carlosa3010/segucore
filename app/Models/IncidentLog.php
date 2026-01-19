<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_id',
        'user_id',
        'action_type', // Ej: SYSTEM, CALL, NOTE, STATUS_CHANGE
        'description',
        'sip_call_id',   // Opcional: ID llamada VoIP
        'recording_url'  // Opcional: Link al audio
    ];

    /**
     * Relación: El log pertenece a un incidente.
     */
    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    /**
     * Relación: El log fue creado por un usuario (Operador).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}