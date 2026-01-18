<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlarmAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 'account_number', 'branch_name', 
        'installation_address', 'latitude', 'longitude',
        'service_status', 'test_mode_until', 'notes'
    ];

    public function customer() { return $this->belongsTo(Customer::class); }
    // ... resto de relaciones
}
