@extends('layouts.admin')

@section('title', 'Gestionando Incidente #' . $incident->id)

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-full">
    
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-red-900/20 border-l-4 border-red-500 p-6 rounded-r-lg shadow-lg">
            <span class="text-red-400 font-bold tracking-widest text-xs uppercase">Evento Crítico</span>
            <h1 class="text-4xl font-bold text-white mt-2">{{ $incident->alarmEvent->event_code }}</h1>
            <p class="text-xl text-red-200 mt-1">{{ $incident->alarmEvent->siaCode->description ?? 'Evento Desconocido' }}</p>
            <div class="mt-4 p-3 bg-black/30 rounded text-sm text-gray-300 font-mono">
                {{ $incident->alarmEvent->raw_data }}
            </div>
        </div>

        <div class="bg-[#1e293b] p-5 rounded-lg border border-gray-700">
            <h3 class="text-gray-400 uppercase text-xs font-bold mb-4">Datos del Abonado</h3>
            <div class="text-white text-lg font-bold mb-1">{{ $incident->alarmEvent->account->customer->full_name }}</div>
            <div class="text-blue-400 text-sm mb-4">{{ $incident->alarmEvent->account->installation_address }}</div>
            
            <div class="grid grid-cols-2 gap-4">
                <a href="tel:{{ $incident->alarmEvent->account->customer->phone_1 }}" class="bg-green-700 hover:bg-green-600 text-white py-2 px-4 rounded text-center block transition">
                    📞 Llamar Titular
                </a>
                <a href="#" class="bg-blue-700 hover:bg-blue-600 text-white py-2 px-4 rounded text-center block transition">
                    📍 Ver en Waze
                </a>
            </div>
        </div>
    </div>

    <div class="lg:col-span-1 bg-[#1e293b] border border-gray-700 rounded-lg p-6 flex flex-col">
        <h3 class="text-white font-bold mb-4 flex items-center gap-2">
            <span>📋</span> Protocolo de Llamadas
        </h3>
        
        <div class="flex-1 overflow-y-auto space-y-3 pr-2">
            @foreach($incident->alarmEvent->account->customer->contacts->sortBy('priority') as $contact)
                <div class="flex justify-between items-center p-4 bg-gray-800 rounded border border-gray-700">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="bg-blue-600 text-xs font-bold px-2 py-0.5 rounded text-white">{{ $contact->priority }}</span>
                            <span class="text-white font-bold">{{ $contact->name }}</span>
                        </div>
                        <span class="text-xs text-gray-400 uppercase">{{ $contact->relationship }}</span>
                    </div>
                    <a href="tel:{{ $contact->phone }}" class="text-green-400 hover:text-white font-mono text-lg font-bold">
                        {{ $contact->phone }} 📞
                    </a>
                </div>
            @endforeach
        </div>

        <div class="mt-6 pt-6 border-t border-gray-700">
            <h4 class="text-gray-400 text-xs uppercase font-bold mb-2">Notas del Operador</h4>
            <textarea class="w-full bg-gray-900 border border-gray-600 text-white p-3 rounded h-32 text-sm focus:border-blue-500 outline-none" placeholder="Registra aquí el resultado de las llamadas..."></textarea>
        </div>
    </div>

    <div class="lg:col-span-1 flex flex-col gap-6">
        <div class="bg-gray-800 rounded-lg border border-gray-700 h-64 flex items-center justify-center relative overflow-hidden group">
            <div class="absolute inset-0 bg-[url('https://maps.googleapis.com/maps/api/staticmap?center={{ $incident->alarmEvent->account->latitude }},{{ $incident->alarmEvent->account->longitude }}&zoom=15&size=600x300&maptype=roadmap&markers=color:red%7C{{ $incident->alarmEvent->account->latitude }},{{ $incident->alarmEvent->account->longitude }}&key=YOUR_API_KEY')] bg-cover bg-center opacity-50 group-hover:opacity-80 transition"></div>
            <span class="relative z-10 bg-black/50 px-3 py-1 rounded text-white text-xs">Mapa de Ubicación</span>
        </div>

        <div class="bg-[#1e293b] border border-gray-700 rounded-lg p-6 flex-1">
            <h3 class="text-white font-bold mb-4">Cerrar Incidente</h3>
            <form action="{{ route('admin.incidents.close', $incident->id) }}" method="POST" class="h-full flex flex-col">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-400 text-xs uppercase mb-2">Resultado</label>
                    <select name="result_code" class="w-full bg-gray-900 border border-gray-600 text-white p-2 rounded">
                        <option value="false_alarm">Falsa Alarma (Usuario canceló)</option>
                        <option value="verified_police">Real - Policía Notificada</option>
                        <option value="test">Prueba de Sistema</option>
                        <option value="no_answer">Sin respuesta de contactos</option>
                    </select>
                </div>
                
                <div class="mb-4 flex-1">
                    <label class="block text-gray-400 text-xs uppercase mb-2">Informe Final</label>
                    <textarea name="resolution_notes" class="w-full bg-gray-900 border border-gray-600 text-white p-3 rounded h-full text-sm resize-none" placeholder="Resumen final para el cliente..." required></textarea>
                </div>

                <button type="submit" class="w-full bg-green-600 hover:bg-green-500 text-white font-bold py-3 rounded shadow-lg transition">
                    ✓ Finalizar Atención
                </button>
            </form>
        </div>
    </div>
</div>
@endsection