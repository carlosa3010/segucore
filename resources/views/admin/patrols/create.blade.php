@extends('layouts.admin')
@section('title', 'Nueva Patrulla')

@section('content')
<div class="bg-slate-900 min-h-screen p-4 flex justify-center">
    <div class="w-full max-w-lg bg-slate-800 rounded-lg border border-slate-700 p-6 shadow-xl">
        <h2 class="text-xl font-bold text-white mb-6">Registrar Unidad de Patrulla</h2>
        
        <form action="{{ route('admin.patrols.store') }}" method="POST">
            @csrf
            
            <div class="space-y-4">
                <div>
                    <label class="block text-xs uppercase text-slate-400 mb-1">Nombre de la Unidad</label>
                    <input type="text" name="name" placeholder="Ej: Móvil Alfa-01" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white" required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs uppercase text-slate-400 mb-1">Tipo</label>
                        <select name="vehicle_type" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white">
                            <option value="car">🚓 Automóvil</option>
                            <option value="motorcycle">🏍️ Motocicleta</option>
                            <option value="bicycle">🚲 Bicicleta</option>
                            <option value="foot">🚶 A Pie (Peatonal)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs uppercase text-slate-400 mb-1">Placa / ID</label>
                        <input type="text" name="plate_number" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white uppercase">
                    </div>
                </div>

                <div>
                    <label class="block text-xs uppercase text-slate-400 mb-1">Dispositivo GPS (Tracking)</label>
                    <select name="gps_device_id" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white">
                        <option value="">-- Sin GPS (Solo App) --</option>
                        @foreach($gpsDevices as $gps)
                            <option value="{{ $gps->id }}">{{ $gps->name }} ({{ $gps->imei }})</option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-slate-500 mt-1">Solo se muestran GPS no asignados a otras patrullas.</p>
                </div>

                <div class="flex items-center gap-2 mt-4 bg-slate-900 p-2 rounded border border-slate-700">
                    <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded bg-slate-700 border-slate-500 text-blue-600">
                    <label class="text-sm text-slate-300">Unidad Activa / En Servicio</label>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('admin.patrols.index') }}" class="text-slate-400 text-sm py-2 px-4 hover:text-white">Cancelar</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-6 rounded-lg">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection