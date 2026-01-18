<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountLog extends Model
{
    protected $fillable = ['alarm_account_id', 'user_id', 'type', 'content'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}