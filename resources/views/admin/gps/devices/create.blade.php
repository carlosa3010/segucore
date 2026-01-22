@extends('layouts.admin')
@section('title', 'Nuevo GPS')

@section('content')
<div class="bg-slate-900 min-h-screen p-4 flex justify-center">
    <div class="w-full max-w-3xl">
        
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-white">📡 Nuevo Dispositivo GPS</h1>
            <a href="{{ route('admin.gps.devices.index') }}" class="text-slate-400 hover:text-white text-sm">← Volver al listado</a>
        </div>

        <form action="{{ route('admin.gps.devices.store') }}" method="POST" class="bg-slate-800 rounded-lg border border-slate-700 p-6 shadow-xl">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Cliente Propietario</label>
                    <select name="customer_id" class="w-full bg-slate-900 border border-slate-600 rounded p-2.5 text-sm text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500" required>
                        <option value="" disabled selected>Seleccione Cliente...</option>
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
                    <label class="block text-xs font-bold uppercase text-yellow-500 mb-2">IMEI (Identificador)</label>
                    <input type="text" name="unique_id" placeholder="Ej: 8654320..." class="w-full bg-slate-900 border border-yellow-600/50 rounded p-2.5 text-sm text-white focus:border-yellow-500 font-mono" required>
                    <p class="text-[10px] text-slate-500 mt-1">Este ID debe ser único en Traccar.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Modelo GPS</label>
                    <input type="text" name="device_model" placeholder="Ej: Coban, Teltonika" class="w-full bg-slate-900 border border-slate-600 rounded p-2.5 text-sm text-white focus:border-blue-500" required>
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 pb-6 border-b border-slate-700">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Número SIM (Línea)</label>
                    <input type="text" name="phone_number" placeholder="+58..." class="w-full bg-slate-900 border border-slate-600 rounded p-2.5 text-sm text-white focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Placa / Patente</label>
                    <input type="text" name="plate_number" placeholder="Ej: AB123CD" class="w-full bg-slate-900 border border-slate-600 rounded p-2.5 text-sm text-white focus:border-blue-500 uppercase">
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.gps.devices.index') }}" class="px-5 py-2.5 rounded-lg border border-slate-600 text-slate-300 hover:bg-slate-700 hover:text-white transition text-sm font-bold">Cancelar</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2.5 rounded-lg font-bold text-sm shadow-lg transition">
                    💾 Guardar y Sincronizar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection