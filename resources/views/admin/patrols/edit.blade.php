@extends('layouts.admin')
@section('title', 'Editar Patrulla')

@section('content')
<div class="bg-slate-900 min-h-screen p-4 flex justify-center">
    <div class="w-full max-w-lg bg-slate-800 rounded-lg border border-slate-700 p-6 shadow-xl">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-white">Editar Unidad</h2>
            <a href="{{ route('admin.patrols.index') }}" class="text-xs text-slate-400 hover:text-white">Volver</a>
        </div>
        
        <form action="{{ route('admin.patrols.update', $patrol->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="space-y-4">
                <div>
                    <label class="block text-xs uppercase text-slate-400 mb-1">Nombre de la Unidad</label>
                    <input type="text" name="name" value="{{ $patrol->name }}" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white" required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs uppercase text-slate-400 mb-1">Tipo</label>
                        <select name="vehicle_type" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white">
                            <option value="car" {{ $patrol->vehicle_type == 'car' ? 'selected' : '' }}>🚓 Automóvil</option>
                            <option value="motorcycle" {{ $patrol->vehicle_type == 'motorcycle' ? 'selected' : '' }}>🏍️ Motocicleta</option>
                            <option value="bicycle" {{ $patrol->vehicle_type == 'bicycle' ? 'selected' : '' }}>🚲 Bicicleta</option>
                            <option value="foot" {{ $patrol->vehicle_type == 'foot' ? 'selected' : '' }}>🚶 A Pie</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs uppercase text-slate-400 mb-1">Placa / ID</label>
                        <input type="text" name="plate_number" value="{{ $patrol->plate_number }}" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white uppercase">
                    </div>
                </div>

                <div>
                    <label class="block text-xs uppercase text-slate-400 mb-1">Dispositivo GPS</label>
                    <select name="gps_device_id" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white">
                        <option value="">-- Sin GPS --</option>
                        @if($patrol->gpsDevice)
                            <option value="{{ $patrol->gps_device_id }}" selected>{{ $patrol->gpsDevice->name }} (Actual)</option>
                        @endif
                        
                        @foreach($gpsDevices as $gps)
                            <option value="{{ $gps->id }}">{{ $gps->name }} ({{ $gps->imei }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2 mt-4 bg-slate-900 p-2 rounded border border-slate-700">
                    <input type="checkbox" name="is_active" value="1" {{ $patrol->is_active ? 'checked' : '' }} class="w-4 h-4 rounded bg-slate-700 border-slate-500 text-blue-600">
                    <label class="text-sm text-slate-300">Unidad Activa</label>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-6 rounded-lg transition">Actualizar</button>
            </div>
        </form>
    </div>
</div>
@endsection