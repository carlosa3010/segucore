<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
    'first_name',
    'national_id',
    'email',
    'phone_primary',
    'address_monitoring',
    // --- AGREGAR ESTOS ---
    'monitoring_password',
    'duress_password',
];
}
