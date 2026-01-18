@extends('layouts.admin')

@section('title', 'Nuevo Código SIA')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">Registrar Código SIA</h1>
        <a href="{{ route('admin.sia-codes.index') }}" class="text-gray-400 hover:text-white">&larr; Cancelar</a>
    </div>

    <form action="{{ route('admin.sia-codes.store') }}" method="POST" class="bg-[#1e293b] p-8 rounded-lg border border-gray-700 shadow-xl">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <div>
                <label class="block text-sm text-gray-400 mb-1">Código SIA (2 Letras) *</label>
                <input type="text" name="code" required maxlength="3" class="form-input uppercase font-mono text-xl" placeholder="BA">
                <p class="text-xs text-gray-500 mt-1">Ej: BA (Burglary Alarm), FA (Fire Alarm)</p>
            </div>

            <div>
                <label class="block text-sm text-gray-400 mb-1">Nivel de Prioridad *</label>
                <select name="priority" class="form-input">
                    <option value="5" class="bg-red-900 text-white">5 - Crítico (Pánico/Fuego)</option>
                    <option value="4" class="bg-orange-900 text-white">4 - Alta (Robo Confirmado)</option>
                    <option value="3" class="bg-yellow-900 text-white">3 - Media (Fallo Técnico)</option>
                    <option value="2">2 - Baja (Mantenimiento)</option>
                    <option value="1">1 - Informativo (Apertura/Cierre)</option>
                    <option value="0">0 - Ignorar (Test)</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm text-gray-400 mb-1">Descripción del Evento *</label>
                <input type="text" name="description" required class="form-input" placeholder="Ej: Alarma de Robo en Zona Interior">
            </div>

            <div>
                <label class="block text-sm text-gray-400 mb-1">Color de Alerta *</label>
                <div class="flex items-center gap-4">
                    <input type="color" name="color_hex" value="#ff4500" class="h-10 w-20 bg-transparent border border-gray-600 rounded cursor-pointer">
                    <span class="text-xs text-gray-500">Clic para seleccionar</span>
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-400 mb-1">Archivo de Sonido</label>
                <select name="sound_alert" class="form-input">
                    <option value="">(Silencioso)</option>
                    <option value="panic.mp3">panic.mp3 (Sirena Policial)</option>
                    <option value="fire_alarm.mp3">fire_alarm.mp3 (Incendio)</option>
                    <option value="burglar.mp3">burglar.mp3 (Intrusión)</option>
                    <option value="warning.mp3">warning.mp3 (Sonar/Beep)</option>
                    <option value="tamper.mp3">tamper.mp3 (Fallo Técnico)</option>
                </select>
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit" class="bg-[#C6F211] hover:bg-[#a3c90d] text-black font-bold py-3 px-8 rounded shadow-lg">
                Guardar Código
            </button>
        </div>
    </form>
</div>
@endsection