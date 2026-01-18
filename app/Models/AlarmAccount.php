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

    protected $casts = [
        'temporary_notes_until' => 'datetime',
        'test_mode_until' => 'datetime',
    ];

    /**
     * Relación: Una cuenta pertenece a un Cliente.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relación: Una cuenta tiene muchas Zonas configuradas.
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
     * Relación: Bitácora de operaciones (Notas, Llamadas, Alertas).
     * Ordenamos por fecha descendente para ver lo último primero.
     */
    public function logs()
    {
        return $this->hasMany(AccountLog::class)->orderBy('created_at', 'desc');
    }

    /**
     * Relación: Eventos de Alarma (Historial técnico SIA).
     * Vinculamos por 'account_number' ya que es el dato que llega del receptor.
     */
    public function events()
    {
        return $this->hasMany(AlarmEvent::class, 'account_number', 'account_number');
    }
}