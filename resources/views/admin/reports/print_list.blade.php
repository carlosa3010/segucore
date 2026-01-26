<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Historial Detallado - {{ $customer ? $customer->full_name : 'Reporte General' }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; }
        
        /* Encabezado Corporativo */
        .header-container { width: 100%; border-bottom: 2px solid #C6F211; padding-bottom: 10px; margin-bottom: 15px; }
        .header-table { width: 100%; border-collapse: collapse; border: none; }
        .header-table td { border: none; vertical-align: middle; }
        
        .logo-img { height: 50px; width: auto; }
        .company-info { text-align: right; font-size: 9px; color: #555; line-height: 1.2; }
        
        .report-title { text-align: center; font-size: 16px; font-weight: bold; text-transform: uppercase; margin: 10px 0; }
        .meta { text-align: center; font-size: 9px; color: #666; margin-bottom: 15px; }

        /* Información del Filtro */
        .filter-info { background: #f8fafc; padding: 10px; border: 1px solid #e2e8f0; margin-bottom: 15px; font-size: 10px; border-radius: 4px; }
        
        /* Tabla de Datos */
        table.data-table { width: 100%; border-collapse: collapse; font-size: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #ccc; padding: 6px; text-align: left; vertical-align: top; }
        table.data-table th { background-color: #000; color: #fff; font-weight: bold; text-transform: uppercase; }
        
        /* Estados */
        .status-real { color: #166534; font-weight: bold; background: #dcfce7; padding: 1px 4px; border-radius: 2px; }
        .status-false { color: #991b1b; font-weight: bold; background: #fee2e2; padding: 1px 4px; border-radius: 2px; }
        .status-auto { color: #64748b; font-style: italic; font-size: 9px; }

        /* Salto de página inteligente */
        tr { page-break-inside: avoid; }
        thead { display: table-header-group; } 
        
        /* Botón imprimir */
        @media print { .no-print { display: none; } }
        .btn-print { background: #333; color: white; padding: 8px 15px; border: none; cursor: pointer; font-weight: bold; border-radius: 4px; }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="text-align: center; margin-bottom: 20px; padding: 10px; background: #eee; border-bottom: 1px solid #ccc;">
        <button onclick="window.print()" class="btn-print">🖨️ IMPRIMIR LISTADO</button>
        <div style="margin-top: 5px; font-size: 10px; color: #666;">Use la configuración de impresión de su navegador para guardar como PDF</div>
    </div>

    <div class="header-container">
        <table class="header-table">
            <tr>
                <td style="text-align: left;">
                    {{-- Logo oficial --}}
                    <img src="{{ public_path('images/logo.png') }}" class="logo-img" alt="SEGUSMART 24">
                </td>
                <td class="company-info">
                    <strong style="font-size: 11px; color: #000;">SEGUSMART 24, C.A.</strong><br>
                    RIF: J-50608166-0<br>
                    Av Lara CC Rio Lama 5ta Etapa Nivel Plaza Local 38-39<br>
                    Barquisimeto, Edo. Lara 3001<br>
                    contacto@segusmart24.com
                </td>
            </tr>
        </table>
    </div>

    <div class="report-title">Reporte Detallado de Eventos de Alarma</div>
    <div class="meta">Generado el: {{ now()->setTimezone('America/Caracas')->format('d/m/Y H:i:s') }} | Operador: {{ auth()->user()->name }}</div>

    <div class="filter-info">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none; width: 50%;">
                    <strong>Cliente:</strong> {{ $customer ? ($customer->business_name ?? $customer->full_name) : 'TODOS (Reporte General)' }}<br>
                    <strong>Rango de Fechas:</strong> {{ $request->date_from }} <strong>al</strong> {{ $request->date_to }}
                </td>
                <td style="border: none; width: 50%;">
                    @if($request->sia_code) <strong>Filtro Evento:</strong> {{ $request->sia_code }} <br> @endif
                    @if($request->status) <strong>Filtro Estado:</strong> {{ strtoupper($request->status) }} @endif
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
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
                <td>{{ $e->created_at->setTimezone('America/Caracas')->format('d/m/Y H:i:s') }}</td>
                <td>
                    <strong>{{ $e->account_number }}</strong><br>
                    {{ Str::limit($e->account->branch_name ?? '', 20) }}
                </td>
                <td style="text-align: center;">{{ $e->event_code }}</td>
                <td>{{ $e->siaCode->description ?? 'Desconocido' }}</td>
                <td style="text-align: center;">{{ $e->zone }}</td>
                <td>
                    @if($e->incident)
                        <div style="margin-bottom: 3px;">
                            @if($e->incident->result == 'false_alarm') 
                                <span class="status-false">[FALSA ALARMA]</span>
                            @elseif(in_array($e->incident->result, ['real_police', 'real_medical', 'real_fire'])) 
                                <span class="status-real">[REAL]</span>
                            @else
                                <strong>[{{ strtoupper($e->incident->result ?? 'EN PROCESO') }}]</strong>
                            @endif
                        </div>
                        <span style="font-size: 9px; color: #444;">{{ Str::limit($e->incident->notes, 100) }}</span>
                    @else
                        <span class="status-auto">Procesado Automáticamente</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px; color: #666;">No se encontraron registros que coincidan con los filtros seleccionados.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="border-top: 1px solid #000; margin-top: 20px; padding-top: 5px; font-size: 9px; text-align: center; color: #555;">
        Fin del Reporte - Total Registros: {{ count($events) }} - Documento generado por Segusmart 24
    </div>

</body>
</html>