<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentHoldReason extends Model
{
    protected $fillable = ['name', 'code', 'is_active'];
}