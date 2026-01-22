<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Resumen Ejecutivo</title>
    <style>
        body { font-family: sans-serif; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .kpi-container { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .kpi-box { flex: 1; text-align: center; border: 1px solid #ddd; padding: 15px; margin: 0 5px; background: #f9f9f9; }
        .kpi-val { font-size: 24px; font-weight: bold; color: #000; display: block; }
        .kpi-label { font-size: 12px; color: #666; text-transform: uppercase; }
        
        .chart-section { margin-bottom: 30px; page-break-inside: avoid; }
        .chart-title { font-weight: bold; border-bottom: 1px solid #ccc; margin-bottom: 10px; padding-bottom: 5px; }
        
        /* Simulación de Gráfico de Barras con CSS */
        .bar-row { display: flex; align-items: center; margin-bottom: 8px; font-size: 11px; }
        .bar-label { width: 150px; text-align: right; padding-right: 10px; }
        .bar-track { flex: 1; background: #eee; height: 15px; border-radius: 3px; overflow: hidden; }
        .bar-fill { height: 100%; background: #3b82f6; text-align: right; color: white; padding-right: 5px; line-height: 15px; font-size: 10px; }
        
        .footer { position: fixed; bottom: 0; left: 0; width: 100%; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <h1 style="margin:0;">SEGUSMART 24 C.A.</h1>
        <h3 style="margin:5px 0; font-weight:normal;">Resumen de Actividad de Monitoreo</h3>
        <small>Cliente: {{ $customer ? $customer->full_name : 'General' }} | {{ $request->date_from }} al {{ $request->date_to }}</small>
    </div>

    <div class="kpi-container">
        <div class="kpi-box">
            <span class="kpi-val">{{ $total }}</span>
            <span class="kpi-label">Eventos Totales</span>
        </div>
        <div class="kpi-box">
            <span class="kpi-val">{{ $incidents }}</span>
            <span class="kpi-label">Incidentes Reales</span>
        </div>
        <div class="kpi-box">
            <span class="kpi-val">{{ $auto }}</span>
            <span class="kpi-label">Señales Automáticas</span>
        </div>
    </div>

    <div class="chart-section">
        <div class="chart-title">Eventos Más Frecuentes</div>
        @foreach($topEvents as $ev)
            @php $width = ($ev->total / $total) * 100; @endphp
            <div class="bar-row">
                <div class="bar-label">{{ $ev->event_code }} - {{ Str::limit($ev->siaCode->description ?? '', 15) }}</div>
                <div class="bar-track">
                    <div class="bar-fill" style="width: {{ $width }}%;">{{ $ev->total }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="chart-section">
        <div class="chart-title">Volumen Diario</div>
        @php $maxDaily = $eventsByDay->max('total'); @endphp
        @foreach($eventsByDay as $day)
            @php $width = $maxDaily > 0 ? ($day->total / $maxDaily) * 100 : 0; @endphp
            <div class="bar-row">
                <div class="bar-label">{{ \Carbon\Carbon::parse($day->date)->format('d/m') }}</div>
                <div class="bar-track">
                    <div class="bar-fill" style="width: {{ $width }}%; background-color: #64748b;">{{ $day->total }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="footer">
        Generado automáticamente por el sistema de gestión Segusmart.
    </div>

</body>
</html>