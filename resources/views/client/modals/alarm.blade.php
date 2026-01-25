<div class="flex flex-col h-full bg-gray-900 text-white">
    <div class="flex justify-between items-center p-4 border-b border-gray-800 bg-black/40">
        <div class="flex items-center gap-3">
            <div class="bg-red-500/20 p-2 rounded text-red-500">
                <i class="fas fa-shield-alt text-xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-lg leading-tight">{{ $account->name ?? 'Cuenta de Alarma' }}</h3>
                <p class="text-xs text-gray-500">ID: {{ $account->account_number }}</p>
            </div>
        </div>
        <button onclick="closeModal()" class="text-gray-400 hover:text-white transition">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>

    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <div class="space-y-4">
            <div class="bg-gray-800 p-4 rounded border border-gray-700 text-center">
                <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">Estado del Sistema</p>
                @if(($account->monitoring_status ?? '') == 'armed')
                    <h2 class="text-2xl font-bold text-green-500"><i class="fas fa-check-circle"></i> ARMADO</h2>
                @elseif(($account->monitoring_status ?? '') == 'alarm')
                    <h2 class="text-2xl font-bold text-red-500 animate-pulse"><i class="fas fa-exclamation-triangle"></i> EN ALARMA</h2>
                @else
                    <h2 class="text-2xl font-bold text-gray-400">DESARMADO</h2>
                @endif
            </div>

            <div class="space-y-2 text-sm">
                <div class="flex justify-between py-2 border-b border-gray-800">
                    <span class="text-gray-500">Dirección</span>
                    <span class="text-gray-300 text-right">{{ $account->address ?? 'No registrada' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-800">
                    <span class="text-gray-500">Última Señal</span>
                    <span class="text-gray-300">{{ $account->updated_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-800">
                    <span class="text-gray-500">Zona Horaria</span>
                    <span class="text-gray-300">{{ $account->timezone ?? 'UTC-4' }}</span>
                </div>
            </div>
        </div>

        <div>
            <h4 class="text-xs font-bold text-gray-500 uppercase mb-3">Eventos Recientes</h4>
            <div class="bg-gray-800 rounded border border-gray-700 overflow-hidden">
                <table class="w-full text-sm text-left">
                    <tbody class="divide-y divide-gray-700">
                        <tr class="hover:bg-gray-700/50">
                            <td class="p-3 text-gray-400">Hace 10 min</td>
                            <td class="p-3 text-white">Test Periódico</td>
                        </tr>
                        <tr class="hover:bg-gray-700/50">
                            <td class="p-3 text-gray-400">Hace 4 hrs</td>
                            <td class="p-3 text-green-400">Apertura Sistema</td>
                        </tr>
                        <tr class="hover:bg-gray-700/50">
                            <td class="p-3 text-gray-400">Ayer 20:00</td>
                            <td class="p-3 text-red-400">Cierre Sistema</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <button class="w-full mt-4 bg-blue-600/20 text-blue-400 border border-blue-600/50 hover:bg-blue-600 hover:text-white py-2 rounded transition text-sm font-bold">
                <i class="fas fa-list-ul mr-2"></i> Ver Historial Completo
            </button>
        </div>
    </div>
</div>