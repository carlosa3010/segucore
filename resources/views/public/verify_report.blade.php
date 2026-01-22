<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Validación de Documento - SEGUCORE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #f3f4f6; }
        .verified-badge { background-color: #d1fae5; color: #065f46; padding: 0.5rem; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; border: 1px solid #34d399; }
    </style>
</head>
<body>
    <div class="max-w-2xl mx-auto p-4">
        
        <div class="verified-badge shadow-sm">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-bold text-lg">DOCUMENTO AUTÉNTICO Y VÁLIDO</span>
        </div>

        <div class="bg-white rounded-lg shadow-xl overflow-hidden border-t-8 border-blue-600">
            
            <div class="p-6 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-800">Informe de Incidente #{{ str_pad($incident->id, 6, '0', STR_PAD_LEFT) }}</h1>
                <p class="text-gray-500 text-sm mt-1">Este reporte digital es una copia fiel de los registros almacenados en el servidor central de Segusmart 24.</p>
                <div class="mt-4 flex justify-between items-center bg-gray-100 p-3 rounded">
                    <span class="text-xs font-bold text-gray-500 uppercase">Hash de Seguridad</span>
                    <span class="font-mono text-lg font-bold text-blue-600">{{ $securityHash }}</span>
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50">
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase mb-1">Cliente</h3>
                    <p class="font-medium text-gray-900">{{ $incident->alarmEvent->account->customer->full_name }}</p>
                    <p class="text-sm text-gray-500">{{ $incident->alarmEvent->account->customer->national_id }}</p>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase mb-1">Cuenta / Abonado</h3>
                    <p class="font-medium text-gray-900">{{ $incident->alarmEvent->account_number }}</p>
                    <p class="text-sm text-gray-500">{{ $incident->alarmEvent->account->branch_name }}</p>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase mb-1">Fecha y Hora</h3>
                    <p class="font-medium text-gray-900">{{ $incident->created_at->format('d/m/Y h:i:s A') }}</p>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase mb-1">Evento</h3>
                    <p class="font-medium text-gray-900">{{ $incident->alarmEvent->event_code }}</p>
                    <p class="text-sm text-gray-500">{{ $incident->alarmEvent->siaCode->description }}</p>
                </div>
            </div>

            <div class="p-6 border-t border-gray-200">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Bitácora de Operaciones</h3>
                <div class="border rounded-lg overflow-hidden text-sm">
                    <table class="w-full text-left">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="p-3 font-bold text-gray-600">Hora</th>
                                <th class="p-3 font-bold text-gray-600">Acción</th>
                                <th class="p-3 font-bold text-gray-600">Detalle</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($incident->logs as $log)
                            <tr>
                                <td class="p-3 whitespace-nowrap text-gray-500">{{ $log->created_at->format('H:i:s') }}</td>
                                <td class="p-3 font-medium text-blue-600">{{ $log->action_type }}</td>
                                <td class="p-3 text-gray-700">{{ $log->description }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="p-6 bg-blue-50 border-t border-blue-100">
                <h3 class="text-xs font-bold text-blue-800 uppercase mb-2">Resolución Final</h3>
                <div class="bg-white p-4 rounded border border-blue-200 shadow-sm">
                    <p class="text-gray-800 italic">"{{ $incident->notes }}"</p>
                    <div class="mt-2 text-right">
                        <span class="text-xs font-bold text-blue-600 uppercase bg-blue-100 px-2 py-1 rounded">
                            {{ $incident->result }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-gray-900 text-center text-gray-400 text-xs">
                Verificado por Segusmart Core &copy; {{ date('Y') }}
            </div>
        </div>
    </div>
</body>
</html>