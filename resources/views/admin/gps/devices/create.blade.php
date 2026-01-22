@extends('layouts.admin')
@section('title', 'Nuevo Dispositivo GPS')

@section('content')
<div class="bg-slate-900 min-h-screen p-4 flex justify-center">
    <div class="w-full max-w-4xl"> <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-white">🛰️ Registrar GPS</h1>
            <a href="{{ route('admin.gps.devices.index') }}" class="text-slate-400 hover:text-white text-sm">← Volver</a>
        </div>

        <form action="{{ route('admin.gps.devices.store') }}" method="POST" class="bg-slate-800 rounded-lg border border-slate-700 p-6 shadow-xl">
            @csrf
            
            <h3 class="text-sm font-bold text-slate-500 uppercase mb-4 border-b border-slate-700 pb-2">Información Básica</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Cliente Propietario</label>
                    <select name="customer_id" class="w-full bg-slate-900 border border-slate-600 rounded p-2.5 text-sm text-white focus:border-blue-500" required>
                        <option value="">-- Seleccionar --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->business_name ?? $c->full_name }} ({{ $c->national_id }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Nombre / Alias</label>
                    <input type="text" name="name" placeholder="Ej: Camión Ford 350" class="w-full bg-slate-900 border border-slate-600 rounded p-2.5 text-sm text-white focus:border-blue-500" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">IMEI (Identificador)</label>
                    <input type="text" name="imei" placeholder="15 dígitos" class="w-full bg-slate-900 border border-slate-600 rounded p-2.5 text-sm text-white focus:border-blue-500 font-mono" required>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Modelo GPS</label>
                    <select name="device_model" class="w-full bg-slate-900 border border-slate-600 rounded p-2.5 text-sm text-white focus:border-blue-500">
                        <option value="coban">Coban (TK103/303)</option>
                        <option value="teltonika">Teltonika (FMB)</option>
                        <option value="concox">Concox / Jimi</option>
                        <option value="ruptela">Ruptela</option>
                        <option value="sinotrack">Sinotrack</option>
                        <option value="other">Otro / Genérico</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Tipo Vehículo</label>
                    <select name="vehicle_type" class="w-full bg-slate-900 border border-slate-600 rounded p-2.5 text-sm text-white focus:border-blue-500">
                        <option value="car">Automóvil</option>
                        <option value="truck">Camión / Carga</option>
                        <option value="motorcycle">Motocicleta</option>
                        <option value="person">Personal / Portátil</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Placa / Patente</label>
                    <input type="text" name="plate_number" class="w-full bg-slate-900 border border-slate-600 rounded p-2.5 text-sm text-white focus:border-blue-500 uppercase">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Número SIM</label>
                    <input type="text" name="phone_number" placeholder="+58..." class="w-full bg-slate-900 border border-slate-600 rounded p-2.5 text-sm text-white focus:border-blue-500">
                </div>
            </div>

            <h3 class="text-sm font-bold text-slate-500 uppercase mb-4 border-b border-slate-700 pb-2 mt-8">Asignaciones Operativas</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Conductor Asignado</label>
                    <select name="driver_id" class="w-full bg-slate-900 border border-slate-600 rounded p-2.5 text-sm text-white focus:border-blue-500">
                        <option value="">-- Sin Conductor --</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->full_name }} ({{ $driver->license_number }})</option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-slate-500 mt-1">El conductor aparecerá en los reportes de ruta.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Geocercas Permitidas</label>
                    <div class="bg-slate-900 border border-slate-600 rounded p-3 h-32 overflow-y-auto custom-scrollbar">
                        @foreach($geofences as $geo)
                        <div class="flex items-center mb-2">
                            <input type="checkbox" name="geofences[]" value="{{ $geo->id }}" id="geo_{{ $geo->id }}" class="w-4 h-4 text-blue-600 bg-gray-700 border-gray-600 rounded focus:ring-blue-600 ring-offset-gray-800">
                            <label for="geo_{{ $geo->id }}" class="ml-2 text-sm font-medium text-gray-300">{{ $geo->name }}</label>
                        </div>
                        @endforeach
                        @if($geofences->isEmpty())
                            <span class="text-xs text-slate-500 italic">No hay geocercas creadas.</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-slate-700 mt-6">
                <a href="{{ route('admin.gps.devices.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-600 text-slate-300 hover:bg-slate-700 hover:text-white transition text-sm font-bold">Cancelar</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2.5 rounded-lg font-bold text-sm shadow-lg transition">
                    Guardar Dispositivo
                </button>
            </div>
        </form>
    </div>
</div>
@endsection