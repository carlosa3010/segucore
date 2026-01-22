@extends('layouts.admin')
@section('title', 'Editar GPS')

@section('content')
<div class="bg-slate-900 min-h-screen p-4 flex justify-center">
    <div class="w-full max-w-4xl">
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-white">✏️ Editar Dispositivo</h1>
            <a href="{{ route('admin.gps.devices.index') }}" class="text-slate-400 hover:text-white text-sm">← Volver</a>
        </div>

        <form action="{{ route('admin.gps.devices.update', $device->id) }}" method="POST" class="bg-slate-800 rounded-lg border border-slate-700 p-6 shadow-xl">
            @csrf
            @method('PUT')
            
            <div class="mb-6 p-4 bg-slate-900/50 rounded border border-slate-700 flex justify-between items-center">
                <div>
                    <span class="block text-xs text-slate-500 uppercase">Identificador (IMEI)</span>
                    <span class="text-lg font-mono text-yellow-500">{{ $device->imei }}</span>
                </div>
                <div class="text-right">
                    <span class="block text-xs text-slate-500 uppercase">Cliente Propietario</span>
                    <span class="text-white font-bold">{{ $device->customer->business_name ?? $device->customer->full_name }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Nombre / Alias</label>
                    <input type="text" name="name" value="{{ $device->name }}" class="w-full bg-slate-900 border border-slate-600 rounded p-2.5 text-sm text-white focus:border-blue-500" required>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Modelo GPS</label>
                    <input type="text" name="device_model" value="{{ $device->device_model }}" class="w-full bg-slate-900 border border-slate-600 rounded p-2.5 text-sm text-white focus:border-blue-500" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Número SIM</label>
                    <input type="text" name="phone_number" value="{{ $device->phone_number }}" class="w-full bg-slate-900 border border-slate-600 rounded p-2.5 text-sm text-white focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Placa</label>
                    <input type="text" name="plate_number" value="{{ $device->plate_number }}" class="w-full bg-slate-900 border border-slate-600 rounded p-2.5 text-sm text-white focus:border-blue-500 uppercase">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Estado</label>
                    <select name="subscription_status" class="w-full bg-slate-900 border border-slate-600 rounded p-2.5 text-sm text-white focus:border-blue-500">
                        <option value="active" {{ $device->subscription_status == 'active' ? 'selected' : '' }}>🟢 Activo</option>
                        <option value="suspended" {{ $device->subscription_status == 'suspended' ? 'selected' : '' }}>🔴 Suspendido</option>
                    </select>
                </div>
            </div>

            <div class="border-t border-slate-700 pt-6 mt-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    <div>
                        <h3 class="text-sm font-bold text-white mb-4 flex items-center gap-2">👮 Conductor</h3>
                        <select name="driver_id" class="w-full bg-slate-900 border border-slate-600 rounded p-2.5 text-sm text-white focus:border-blue-500">
                            <option value="">-- Sin Conductor --</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ $device->driver_id == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold text-white mb-4 flex items-center gap-2">🌐 Geocercas</h3>
                        <div class="bg-slate-900 border border-slate-600 rounded p-3 h-32 overflow-y-auto custom-scrollbar">
                            @foreach($geofences as $geo)
                            <div class="flex items-center mb-2">
                                <input type="checkbox" name="geofences[]" value="{{ $geo->id }}" id="geo_{{ $geo->id }}" 
                                       class="w-4 h-4 text-blue-600 bg-gray-700 border-gray-600 rounded focus:ring-blue-600"
                                       {{ $device->geofences->contains($geo->id) ? 'checked' : '' }}>
                                <label for="geo_{{ $geo->id }}" class="ml-2 text-sm font-medium text-gray-300">{{ $geo->name }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-700 pt-6 mt-6">
                <h3 class="text-sm font-bold text-white mb-4 flex items-center gap-2">⚠️ Configuración de Alertas</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Límite de Velocidad (Km/h)</label>
                        <input type="number" name="speed_limit" value="{{ $device->speed_limit ?? 80 }}" class="w-full bg-slate-900 border border-slate-600 rounded p-2.5 text-sm text-white focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Odómetro (Km)</label>
                        <input type="number" name="odometer" value="{{ $device->odometer ?? 0 }}" class="w-full bg-slate-900 border border-slate-600 rounded p-2.5 text-sm text-white focus:border-blue-500" step="0.1">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-slate-700 mt-6">
                <a href="{{ route('admin.gps.devices.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-600 text-slate-300 hover:bg-slate-700 hover:text-white transition text-sm font-bold">Cancelar</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2.5 rounded-lg font-bold text-sm shadow-lg transition">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection