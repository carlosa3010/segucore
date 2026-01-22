<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class GeneralSettingController extends Controller
{
    public function index()
    {
        // Traemos todo y lo formateamos como array key => value
        $settings = Setting::all()->pluck('value', 'key');
        return view('admin.config.general', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except(['_token', '_method']);

        foreach ($data as $key => $value) {
            // Determinar grupo basado en prefijo (simple logic)
            $group = 'general';
            if (str_starts_with($key, 'api_')) $group = 'api';
            if (str_starts_with($key, 'mail_')) $group = 'mail';
            if (str_starts_with($key, 'sip_')) $group = 'sip';

            Setting::set($key, $value, $group);
        }

        return back()->with('success', 'Configuraciones actualizadas correctamente.');
    }
}