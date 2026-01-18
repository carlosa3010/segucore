<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlarmPartition extends Model
{
    use HasFactory;

    protected $fillable = [
        'alarm_account_id',
        'partition_number',
        'name',
        'description'
    ];

    public function account()
    {
        return $this->belongsTo(AlarmAccount::class, 'alarm_account_id');
    }
}