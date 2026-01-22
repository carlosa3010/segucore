@extends('layouts.admin')

@section('title', 'Editar Código SIA: ' . $siaCode->code)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">Editar Código SIA</h1>
        <a href="{{ route('admin.sia-codes.index') }}" class="text-gray-400 hover:text-white transition">&larr; Volver al listado</a>
    </div>

    <form action="{{ route('admin.sia-codes.update', $siaCode->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        
        <div class="bg-[#1e293b] p-8 rounded-lg border border-gray-700 shadow-xl">
            <h3 class="text-white font-bold mb-4 border-b border-gray-700 pb-2">Información General</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Código SIA (2 Letras) *</label>
                    <input type="text" name="code" required maxlength="3" 
                           class="form-input uppercase font-mono text-xl bg-gray-800 text-gray-300 border-gray-600 focus:border-blue-500" 
                           value="{{ old('code', $siaCode->code) }}" 
                           placeholder="BA">
                    @error('code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-1">Nivel de Prioridad *</label>
                    <select name="priority" class="form-input bg-gray-800 text-gray-300 border-gray-600 focus:border-blue-500">
                        <option value="5" class="bg-red-900 text-white" {{ $siaCode->priority == 5 ? 'selected' : '' }}>5 - Crítico (Pánico/Fuego)</option>
                        <option value="4" class="bg-orange-900 text-white" {{ $siaCode->priority == 4 ? 'selected' : '' }}>4 - Alta (Robo Confirmado)</option>
                        <option value="3" class="bg-yellow-900 text-white" {{ $siaCode->priority == 3 ? 'selected' : '' }}>3 - Media (Fallo Técnico)</option>
                        <option value="2" {{ $siaCode->priority == 2 ? 'selected' : '' }}>2 - Baja (Mantenimiento)</option>
                        <option value="1" {{ $siaCode->priority == 1 ? 'selected' : '' }}>1 - Informativo (Apertura/Cierre)</option>
                        <option value="0" {{ $siaCode->priority == 0 ? 'selected' : '' }}>0 - Ignorar (Test)</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm text-gray-400 mb-1">Descripción del Evento *</label>
                    <input type="text" name="description" required 
                           class="form-input bg-gray-800 text-gray-300 border-gray-600 focus:border-blue-500" 
                           value="{{ old('description', $siaCode->description) }}" 
                           placeholder="Ej: Alarma de Robo en Zona Interior">
                    @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-1">Color de Alerta</label>
                    <div class="flex items-center gap-4">
                        <input type="color" name="color_hex" 
                               class="h-10 w-20 bg-transparent border border-gray-600 rounded cursor-pointer"
                               value="{{ old('color_hex', $siaCode->color_hex ?? '#ff0000') }}">
                        <span class="text-xs text-gray-500">Clic para cambiar visualización en consola</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-1">Alerta Sonora</label>
                    <select name="sound_alert" class="form-input bg-gray-800 text-gray-300 border-gray-600 focus:border-blue-500">
                        <option value="" {{ $siaCode->sound_alert == '' ? 'selected' : '' }}>(Silencioso)</option>
                        <option value="panic.mp3" {{ $siaCode->sound_alert == 'panic.mp3' ? 'selected' : '' }}>📢 Sirena Policial (Pánico)</option>
                        <option value="fire_alarm.mp3" {{ $siaCode->sound_alert == 'fire_alarm.mp3' ? 'selected' : '' }}>🔥 Alarma de Incendio</option>
                        <option value="burglar.mp3" {{ $siaCode->sound_alert == 'burglar.mp3' ? 'selected' : '' }}>🚨 Sirena Intrusión</option>
                        <option value="warning.mp3" {{ $siaCode->sound_alert == 'warning.mp3' ? 'selected' : '' }}>⚠️ Sonar/Beep Advertencia</option>
                        <option value="tamper.mp3" {{ $siaCode->sound_alert == 'tamper.mp3' ? 'selected' : '' }}>🔧 Tamper Técnico</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-slate-900 p-8 rounded-lg border border-slate-700 shadow-xl">
            <h3 class="text-white font-bold mb-4 border-b border-slate-700 pb-2 flex items-center gap-2">
                🤖 Procesamiento Automático
                <span class="text-[10px] bg-blue-900 text-blue-300 px-2 py-0.5 rounded border border-blue-800">AVANZADO</span>
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="col-span-2">
                    <label class="block text-sm text-gray-400 mb-1">Instrucciones de Procedimiento (SOP)</label>
                    <textarea name="procedure_instructions" rows="4" 
                              class="form-input bg-slate-800 text-white border-slate-600 focus:border-blue-500" 
                              placeholder="Ej: 1. Llamar al titular. 2. Si no responde, llamar a contactos. 3. Despachar patrulla.">{{ old('procedure_instructions', $siaCode->procedure_instructions) }}</textarea>
                    <p class="text-[10px] text-slate-500 mt-1">Este texto aparecerá al operador cuando llegue este evento.</p>
                </div>

                <div class="bg-slate-800 p-4 rounded border border-slate-700">
                    <label class="block text-sm text-white font-bold mb-2">Control de Horarios</label>
                    
                    <div class="flex items-center gap-3 mb-3">
                        <input type="hidden" name="requires_schedule_check" value="0">
                        <input type="checkbox" name="requires_schedule_check" value="1" 
                               {{ $siaCode->requires_schedule_check ? 'checked' : '' }}
                               class="w-4 h-4 rounded bg-slate-700 border-slate-500 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-300">Validar Horario (Apertura/Cierre)</span>
                    </div>

                    <p class="text-[10px] text-slate-500 mb-3">Si se marca, el sistema comparará la hora del evento con el horario del cliente.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs uppercase text-slate-400 mb-1">Tolerancia (Minutos)</label>
                        <input type="number" name="schedule_grace_minutes" 
                               value="{{ old('schedule_grace_minutes', $siaCode->schedule_grace_minutes ?? 30) }}" 
                               class="form-input bg-slate-800 text-white border-slate-600 w-full">
                    </div>
                    
                    <div>
                        <label class="block text-xs uppercase text-slate-400 mb-1">Acción si fuera de horario</label>
                        <select name="schedule_violation_action" class="form-input bg-slate-800 text-white border-slate-600 w-full">
                            <option value="none" {{ $siaCode->schedule_violation_action == 'none' ? 'selected' : '' }}>Solo Registrar en Log</option>
                            <option value="warning" {{ $siaCode->schedule_violation_action == 'warning' ? 'selected' : '' }}>⚠️ Generar Advertencia</option>
                            <option value="critical_alert" {{ $siaCode->schedule_violation_action == 'critical_alert' ? 'selected' : '' }}>🚨 Crear Incidente Crítico</option>
                        </select>
                    </div>
                </div>

                <div class="col-span-2 bg-blue-900/20 p-4 rounded border border-blue-900/50 flex gap-3">
                    <span class="text-2xl">💡</span>
                    <div>
                        <h4 class="text-sm font-bold text-blue-300">Autoprocesamiento</h4>
                        <p class="text-xs text-blue-200 mt-1">
                            Si la prioridad es <strong>0</strong> o <strong>1</strong>, el evento se cerrará automáticamente a menos que haya una violación de horario configurada arriba.
                        </p>
                    </div>
                </div>

            </div>
        </div>

        <div class="flex justify-end gap-4 pt-4">
            <a href="{{ route('admin.sia-codes.index') }}" class="px-6 py-3 rounded text-gray-400 hover:bg-gray-800 transition">Cancelar</a>
            <button type="submit" class="bg-[#C6F211] hover:bg-[#a3c90d] text-black font-bold py-3 px-8 rounded shadow-lg transform hover:scale-105 transition">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>
@endsection