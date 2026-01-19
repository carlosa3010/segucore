<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'priority',    // <--- Nuevo
        'name',
        'relationship',
        'phone',       // <--- Soluciona el error
        'is_active'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}