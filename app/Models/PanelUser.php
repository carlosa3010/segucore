<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PanelUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'alarm_account_id',
        'user_number', // Slot en el panel (001, 002...)
        'name',        // Nombre de la persona (Ej: Gerente Pedro)
        'role'         // master, user, duress, etc.
    ];

    public function account()
    {
        return $this->belongsTo(AlarmAccount::class, 'alarm_account_id');
    }
}