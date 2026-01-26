<div class="flex flex-col h-full bg-gray-900 text-white max-h-[80vh]">
    <div class="flex justify-between items-center p-4 border-b border-gray-800 bg-black/40">
        <div class="flex items-center gap-3">
            <div class="bg-blue-500/20 p-2 rounded text-blue-500">
                <i class="fas fa-file-invoice-dollar text-xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-lg leading-tight">Mis Facturas</h3>
                <p class="text-xs text-gray-500">Historial de pagos y servicios</p>
            </div>
        </div>
        <button onclick="closeModal()" class="text-gray-400 hover:text-white transition">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>

    <div class="p-0 overflow-auto">
        @if($invoices->count() > 0)
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-800 sticky top-0">
                    <tr>
                        <th class="px-6 py-3">Número</th>
                        <th class="px-6 py-3">Fecha</th>
                        <th class="px-6 py-3 text-right">Monto</th>
                        <th class="px-6 py-3 text-center">Estado</th>
                        <th class="px-6 py-3 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach($invoices as $inv)
                        <tr class="hover:bg-gray-800/50 transition">
                            <td class="px-6 py-4 font-mono text-blue-400">{{ $inv->invoice_number }}</td>
                            <td class="px-6 py-4 text-gray-300">{{ $inv->issue_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-right font-bold text-white">${{ number_format($inv->total, 2) }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($inv->status == 'paid')
                                    <span class="bg-green-900/30 text-green-400 text-[10px] px-2 py-1 rounded border border-green-800">PAGADO</span>
                                @elseif($inv->status == 'unpaid')
                                    <span class="bg-red-900/30 text-red-400 text-[10px] px-2 py-1 rounded border border-red-800">PENDIENTE</span>
                                @else
                                    <span class="bg-gray-700 text-gray-300 text-[10px] px-2 py-1 rounded">{{ strtoupper($inv->status) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('client.invoice.download', $inv->id) }}" class="text-gray-400 hover:text-white transition" title="Descargar PDF">
                                    <i class="fas fa-download"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="p-12 text-center text-gray-500">
                <i class="fas fa-folder-open text-4xl mb-3 opacity-30"></i>
                <p>No tiene facturas generadas.</p>
            </div>
        @endif
    </div>
</div>