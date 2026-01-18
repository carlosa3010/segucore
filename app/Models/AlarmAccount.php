<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlarmAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 
        'account_number', 
        'branch_name', 
        'installation_address', 
        'latitude', 
        'longitude',
        'service_status', 
        'test_mode_until', 
        'device_model',          // Nuevo
        'notes',
        'permanent_notes',       // Nuevo
        'temporary_notes',       // Nuevo
        'temporary_notes_until'  // Nuevo
    ];

    /**
     * Relación: Una cuenta pertenece a un Cliente.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relación: Una cuenta tiene muchas Zonas.
     */
    public function zones()
    {
        return $this->hasMany(AlarmZone::class);
    }

    /**
     * Relación: Una cuenta tiene muchas Particiones (Áreas).
     */
    public function partitions()
    {
        return $this->hasMany(AlarmPartition::class);
    }

    /**
     * Relación: Una cuenta tiene muchos Usuarios de Panel (Claves).
     */
    public function panelUsers()
    {
        return $this->hasMany(PanelUser::class);
    }

    /**
     * Relación: Una cuenta tiene muchos Horarios (Schedules).
     */
    public function schedules()
    {
        return $this->hasMany(AccountSchedule::class);
    }

    /**
     * Relación: Eventos de Alarma (Historial).
     */
    public function events()
    {
        return $this->hasMany(AlarmEvent::class, 'account_number', 'account_number');
    }
}