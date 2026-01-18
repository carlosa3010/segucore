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
        'service_status',    // 'active', 'suspended', 'cancelled'
        'test_mode_until',   // Para evitar falsas alarmas durante mantenimiento
        'notes'              // Agregado por si decides usar notas en el futuro (requiere migración)
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
     * Relación: Una cuenta tiene muchos Eventos de Alarma (Historial).
     * Vinculamos por 'account_number' ya que es el dato que llega del receptor SIA.
     */
    public function events()
    {
        return $this->hasMany(AlarmEvent::class, 'account_number', 'account_number');
    }
}
