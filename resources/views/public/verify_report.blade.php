<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificación de Documento - SEGUCORE</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full overflow-hidden border-t-4 border-green-500">
        <div class="p-6 text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Documento Válido</h2>
            <p class="text-sm text-gray-500 mb-6">El informe de incidente ha sido verificado en nuestros registros.</p>
            
            <div class="bg-gray-50 rounded p-4 text-left space-y-3 text-sm border border-gray-200">
                <div class="flex justify-between">
                    <span class="text-gray-500">Incidente ID:</span>
                    <span class="font-mono font-bold">{{ str_pad($incident->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Hash de Seguridad:</span>
                    <span class="font-mono font-bold text-blue-600 tracking-widest">{{ $securityHash }}</span>
                </div>
                <div class="border-t border-gray-200 my-2"></div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Fecha:</span>
                    <span class="font-medium">{{ $incident->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Cliente:</span>
                    <span class="font-medium truncate max-w-[150px]">{{ $incident->alarmEvent->account->customer->full_name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Evento:</span>
                    <span class="font-bold text-gray-700">{{ $incident->alarmEvent->event_code }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Resolución:</span>
                    <span class="uppercase font-bold text-xs bg-gray-200 px-2 py-0.5 rounded">{{ $incident->result }}</span>
                </div>
            </div>

            <p class="text-xs text-gray-400 mt-6">
                Verificado por el sistema de trazabilidad digital de Segusmart 24 C.A.<br>
                {{ now()->format('d/m/Y H:i:s') }}
            </p>
        </div>
    </div>
</body>
</html>