@extends('layouts.admin')
@section('title', 'Editar GPS')

@section('content')
<div class="bg-slate-900 min-h-screen p-4 flex justify-center">
    <div class="w-full max-w-3xl">
        
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
                    <span class="text-lg font-mono text-yellow-500">{{ $device->unique_id }}</span>
                </div>
                <div>
                    <span class="block text-xs text-slate-500 uppercase">Cliente</span>
                    <span class="text-white">{{ $device->customer->business_name ?? $device->customer->full_name }}</span>
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
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Estado Suscripción</label>
                    <select name="subscription_status" class="w-full bg-slate-900 border border-slate-600 rounded p-2.5 text-sm text-white focus:border-blue-500">
                        <option value="active" {{ $device->subscription_status == 'active' ? 'selected' : '' }}>🟢 Activo</option>
                        <option value="suspended" {{ $device->subscription_status == 'suspended' ? 'selected' : '' }}>🔴 Suspendido</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-slate-700">
                <a href="{{ route('admin.gps.devices.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-600 text-slate-300 hover:bg-slate-700 hover:text-white transition text-sm font-bold">Cancelar</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2.5 rounded-lg font-bold text-sm shadow-lg transition">
                    Actualizar Datos
                </button>
            </div>
        </form>
    </div>
</div>
@endsection