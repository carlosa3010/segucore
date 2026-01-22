<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group'];
    
    // Helper para obtener valores rápido
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
    
    // Helper para guardar
    public static function set($key, $value, $group = 'general')
    {
        self::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
    }
}