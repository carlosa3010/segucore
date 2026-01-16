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
        'service_status',
        'test_mode_until',
    ];

    // --- ESTA ES LA FUNCIÓN QUE FALTABA ---
    // Conecta la Cuenta con su Dueño (Cliente)
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    
    // Conecta la Cuenta con sus Zonas
    public function zones()
    {
        return $this->hasMany(AlarmZone::class);
    }
}
