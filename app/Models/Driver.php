<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = ['full_name', 'license_number', 'phone', 'email', 'photo_path', 'status'];

    public function devices()
    {
        return $this->hasMany(GpsDevice::class);
    }
}