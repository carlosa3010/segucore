<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Validación de Documento - SEGUSMART</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #111827; /* Fondo oscuro tipo panel */ }
        .brand-accent { color: #C6F211; }
        .brand-border { border-color: #C6F211; }
        .brand-bg { background-color: #C6F211; color: #000; }
    </style>
</head>
<body>
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        
        <img src="{{ asset('images/logo.png') }}" alt="SEGUSMART" class="h-16 mb-6 object-contain">

        <div class="w-full max-w-2xl bg-white rounded-lg shadow-2xl overflow-hidden relative">
            
            <div class="bg-emerald-500 text-white text-center py-2 font-bold text-sm tracking-widest uppercase flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Documento Auténtico y Vigente
            </div>

            <div class="p-8 border-b border-gray-100">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900">Informe de Incidente</h1>
                        <p class="text-gray-500 mt-1">Ref: #{{ str_pad($incident->id, 6, '0', STR_PAD_LEFT) }}</p>
                    </div>
                    <div class="text-right hidden sm:block">
                        <span class="block text-xs font-bold text-gray-400 uppercase">Fecha de Emisión</span>
                        <span class="block font-mono font-bold text-gray-800">{{ $incident->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>

                <div class="mt-6 bg-gray-50 rounded-md p-4 border border-gray-200 flex flex-col sm:flex-row justify-between items-center">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 sm:mb-0">Serial de Seguridad (Hash)</span>
                    <code class="font-mono text-lg font-bold text-slate-700 bg-white px-3 py-1 rounded border">{{ $securityHash }}</code>
                </div>
            </div>

            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <div class="space-y-6">
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Cliente / Abonado</h3>
                        <p class="text-lg font-bold text-gray-900">{{ $incident->alarmEvent->account->customer->full_name }}</p>
                        <p class="text-sm text-gray-600">{{ $incident->alarmEvent->account->customer->national_id }}</p>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Ubicación / Cuenta</h3>
                        <p class="font-medium text-gray-800">{{ $incident->alarmEvent->account->branch_name }}</p>
                        <div class="flex items-center mt-1">
                            <span class="bg-gray-200 text-gray-700 text-xs px-2 py-0.5 rounded font-mono mr-2">{{ $incident->alarmEvent->account_number }}</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Evento Registrado</h3>
                        <div class="flex items-start">
                            <div class="brand-bg font-bold px-2 py-1 text-sm rounded mr-3">
                                {{ $incident->alarmEvent->event_code }}
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">{{ $incident->alarmEvent->siaCode->description }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $incident->created_at->format('h:i:s A') }} (Hora Local)</p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Resolución Final</h3>
                        <p class="font-medium text-gray-800 italic">"{{ $incident->notes }}"</p>
                        <span class="inline-block mt-2 text-xs font-bold text-slate-600 uppercase border border-slate-300 px-2 py-1 rounded">
                            {{ $incident->result }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 border-t border-gray-200 p-6">
                <h3 class="text-xs font-bold text-gray-500 uppercase mb-4">Traza de Auditoría</h3>
                <div class="space-y-3">
                    @foreach($incident->logs->take(3) as $log)
                    <div class="flex text-sm">
                        <span class="font-mono text-gray-400 w-16 text-xs pt-0.5">{{ $log->created_at->format('H:i') }}</span>
                        <div class="flex-1">
                            <span class="font-bold text-slate-700 text-xs uppercase mr-2">{{ $log->action_type }}</span>
                            <span class="text-gray-600">{{ Str::limit($log->description, 60) }}</span>
                        </div>
                    </div>
                    @endforeach
                    @if($incident->logs->count() > 3)
                        <div class="text-center mt-2">
                            <span class="text-xs text-gray-400 italic">... y {{ $incident->logs->count() - 3 }} eventos más.</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-slate-900 p-4 text-center">
                <p class="text-gray-400 text-xs">
                    Plataforma de Operaciones <span class="text-white font-bold">SEGUSMART CORE</span> &copy; {{ date('Y') }}
                </p>
                <p class="text-gray-600 text-[10px] mt-1">La información contenida en este reporte es confidencial.</p>
            </div>
        </div>
    </div>
</body>
</html>