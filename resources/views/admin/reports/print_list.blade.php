<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Historial Detallado - {{ $customer ? $customer->full_name : 'Reporte General' }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; }
        
        /* Encabezado */
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .logo { font-size: 18px; font-weight: bold; text-transform: uppercase; }
        .meta { font-size: 10px; color: #555; }

        /* Información del Filtro */
        .filter-info { background: #f4f4f4; padding: 10px; border: 1px solid #ddd; margin-bottom: 15px; }
        
        /* Tabla de Datos */
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; vertical-align: top; }
        th { background-color: #e2e2e2; font-weight: bold; text-transform: uppercase; }
        
        /* Estados */
        .status-real { color: #166534; font-weight: bold; }
        .status-false { color: #991b1b; font-weight: bold; }
        .status-auto { color: #64748b; font-style: italic; }

        /* Salto de página inteligente */
        tr { page-break-inside: avoid; }
        thead { display: table-header-group; } 
        
        /* Botón imprimir */
        @media print { .no-print { display: none; } }
        .btn-print { background: #333; color: white; padding: 10px 20px; border: none; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="text-align: center; margin-bottom: 20px; padding: 10px; background: #eee;">
        <button onclick="window.print()" class="btn-print">🖨️ IMPRIMIR LISTADO</button>
        <div style="margin-top: 5px; font-size: 10px; color: #666;">Use la configuración de impresión para guardar como PDF</div>
    </div>

    <div class="header">
        <div class="logo">SEGUSMART 24 C.A.</div>
        <div>Reporte Detallado de Eventos de Alarma</div>
        <div class="meta">Generado el: {{ now()->format('d/m/Y H:i') }} | Usuario: {{ auth()->user()->name }}</div>
    </div>

    <div class="filter-info">
        <strong>Cliente:</strong> {{ $customer ? ($customer->business_name ?? $customer->full_name) : 'TODOS' }}<br>
        <strong>Rango de Fechas:</strong> {{ $request->date_from }} <strong>al</strong> {{ $request->date_to }}<br>
        @if($request->sia_code) <strong>Filtro Evento:</strong> {{ $request->sia_code }} <br> @endif
        @if($request->status) <strong>Filtro Estado:</strong> {{ strtoupper($request->status) }} @endif
    </div>

    <table>
        <thead>
            <tr>
                <th width="12%">Fecha/Hora</th>
                <th width="15%">Cuenta</th>
                <th width="8%">Cód</th>
                <th width="25%">Descripción</th>
                <th width="5%">Zona</th>
                <th width="35%">Resolución / Notas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($events as $e)
            <tr>
                <td>{{ $e->created_at->format('d/m/Y H:i:s') }}</td>
                <td>
                    <strong>{{ $e->account_number }}</strong><br>
                    {{ Str::limit($e->account->branch_name ?? '', 15) }}
                </td>
                <td>{{ $e->event_code }}</td>
                <td>{{ $e->siaCode->description ?? 'Desconocido' }}</td>
                <td style="text-align: center;">{{ $e->zone }}</td>
                <td>
                    @if($e->incident)
                        <div style="margin-bottom: 2px;">
                            @if($e->incident->result == 'false_alarm') 
                                <span class="status-false">[FALSA ALARMA]</span>
                            @elseif(in_array($e->incident->result, ['real_police', 'real_medical', 'real_fire'])) 
                                <span class="status-real">[REAL]</span>
                            @else
                                <strong>[{{ strtoupper($e->incident->result ?? 'EN PROCESO') }}]</strong>
                            @endif
                        </div>
                        {{ $e->incident->notes }}
                    @else
                        <span class="status-auto">Procesado Automáticamente</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px;">No se encontraron registros para este periodo.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="border-top: 1px solid #000; margin-top: 20px; padding-top: 5px; font-size: 9px; text-align: center;">
        Fin del Reporte - Total Registros: {{ count($events) }}
    </div>

</body>
</html>