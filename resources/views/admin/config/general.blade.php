@extends('layouts.admin')
@section('title', 'Ajustes Generales')

@section('content')
<div class="bg-slate-900 min-h-screen p-4" x-data="{ tab: 'general' }">
    <h1 class="text-2xl font-bold text-white mb-6">⚙️ Configuración del Sistema</h1>

    <div class="flex border-b border-slate-700 mb-6">
        <button @click="tab = 'general'" :class="tab === 'general' ? 'border-blue-500 text-blue-400' : 'border-transparent text-slate-400 hover:text-white'" class="px-4 py-2 border-b-2 font-medium text-sm transition">
            🏢 Empresa
        </button>
        <button @click="tab = 'api'" :class="tab === 'api' ? 'border-blue-500 text-blue-400' : 'border-transparent text-slate-400 hover:text-white'" class="px-4 py-2 border-b-2 font-medium text-sm transition">
            🔗 Integraciones API
        </button>
        <button @click="tab = 'notifications'" :class="tab === 'notifications' ? 'border-blue-500 text-blue-400' : 'border-transparent text-slate-400 hover:text-white'" class="px-4 py-2 border-b-2 font-medium text-sm transition">
            📨 Correo & SMS
        </button>
    </div>

    <form action="{{ route('admin.config.general.update') }}" method="POST">
        @csrf
        
        <div x-show="tab === 'general'" class="space-y-4 max-w-2xl">
            <div class="bg-slate-800 p-6 rounded-lg border border-slate-700">
                <h3 class="text-white font-bold mb-4">Información Corporativa</h3>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="text-xs text-slate-400 uppercase">Nombre de la Empresa</label>
                        <input type="text" name="company_name" value="{{ $settings['company_name'] ?? 'SeguSmart 24' }}" class="form-input">
                    </div>
                    <div>
                        <label class="text-xs text-slate-400 uppercase">Dirección Física</label>
                        <input type="text" name="company_address" value="{{ $settings['company_address'] ?? '' }}" class="form-input">
                    </div>
                    <div>
                        <label class="text-xs text-slate-400 uppercase">Teléfono Soporte</label>
                        <input type="text" name="company_phone" value="{{ $settings['company_phone'] ?? '' }}" class="form-input">
                    </div>
                    <div>
                        <label class="text-xs text-slate-400 uppercase">Email Contacto</label>
                        <input type="email" name="company_email" value="{{ $settings['company_email'] ?? '' }}" class="form-input">
                    </div>
                </div>
            </div>
        </div>

        <div x-show="tab === 'api'" class="space-y-4 max-w-2xl" x-cloak>
            
            <div class="bg-slate-800 p-6 rounded-lg border border-slate-700">
                <h3 class="text-white font-bold mb-4 flex items-center gap-2">📡 Servidor GPS (Traccar)</h3>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="text-xs text-slate-400 uppercase">URL Base API</label>
                        <input type="text" name="api_traccar_url" value="{{ $settings['api_traccar_url'] ?? config('services.traccar.base_url') }}" class="form-input">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-slate-400 uppercase">Usuario Admin</label>
                            <input type="text" name="api_traccar_user" value="{{ $settings['api_traccar_user'] ?? '' }}" class="form-input">
                        </div>
                        <div>
                            <label class="text-xs text-slate-400 uppercase">Contraseña</label>
                            <input type="password" name="api_traccar_pass" value="{{ $settings['api_traccar_pass'] ?? '' }}" class="form-input">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-slate-800 p-6 rounded-lg border border-slate-700">
                <h3 class="text-white font-bold mb-4 flex items-center gap-2">🗺️ Google Maps</h3>
                <div>
                    <label class="text-xs text-slate-400 uppercase">API Key (Javascript Maps)</label>
                    <input type="password" name="api_google_maps_key" value="{{ $settings['api_google_maps_key'] ?? '' }}" class="form-input">
                </div>
            </div>

            <div class="bg-slate-800 p-6 rounded-lg border border-slate-700">
                <h3 class="text-white font-bold mb-4 flex items-center gap-2">📞 Telefonía SIP (VoIP)</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-slate-400 uppercase">Servidor SIP (WSS)</label>
                        <input type="text" name="sip_server" value="{{ $settings['sip_server'] ?? '' }}" class="form-input" placeholder="wss://sip.miempresa.com:8089/ws">
                    </div>
                    <div>
                        <label class="text-xs text-slate-400 uppercase">Dominio / Realm</label>
                        <input type="text" name="sip_domain" value="{{ $settings['sip_domain'] ?? '' }}" class="form-input">
                    </div>
                </div>
            </div>
        </div>

        <div x-show="tab === 'notifications'" class="space-y-4 max-w-2xl" x-cloak>
            <div class="bg-slate-800 p-6 rounded-lg border border-slate-700">
                <h3 class="text-white font-bold mb-4">Servidor de Correo (SMTP)</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="text-xs text-slate-400 uppercase">Host</label>
                        <input type="text" name="mail_host" value="{{ $settings['mail_host'] ?? '' }}" class="form-input">
                    </div>
                    <div>
                        <label class="text-xs text-slate-400 uppercase">Puerto</label>
                        <input type="text" name="mail_port" value="{{ $settings['mail_port'] ?? '587' }}" class="form-input">
                    </div>
                    <div>
                        <label class="text-xs text-slate-400 uppercase">Usuario</label>
                        <input type="text" name="mail_username" value="{{ $settings['mail_username'] ?? '' }}" class="form-input">
                    </div>
                </div>
            </div>
            
            <div class="bg-slate-800 p-6 rounded-lg border border-slate-700">
                <h3 class="text-white font-bold mb-4">Plantillas de Mensaje</h3>
                <div>
                    <label class="text-xs text-slate-400 uppercase">Pie de Firma (Emails)</label>
                    <textarea name="mail_footer" rows="3" class="form-input">{{ $settings['mail_footer'] ?? '' }}</textarea>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <button type="submit" class="bg-green-600 hover:bg-green-500 text-white font-bold py-2 px-6 rounded transition">
                Guardar Configuración
            </button>
        </div>
    </form>
</div>
@endsection