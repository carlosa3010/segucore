<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Resumen Ejecutivo - {{ $customer ? $customer->full_name : 'General' }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        
        /* Encabezado Corporativo */
        .header-container { width: 100%; border-bottom: 3px solid #C6F211; padding-bottom: 10px; margin-bottom: 25px; }
        .header-table { width: 100%; border-collapse: collapse; border: none; }
        .header-table td { border: none; vertical-align: middle; }
        
        .logo-img { height: 55px; width: auto; }
        .company-info { text-align: right; font-size: 10px; color: #555; line-height: 1.3; }

        .report-title { text-align: center; font-size: 20px; font-weight: bold; text-transform: uppercase; margin: 10px 0 5px 0; color: #1e293b; }
        .report-meta { text-align: center; font-size: 11px; color: #64748b; margin-bottom: 30px; }

        /* KPIs (Indicadores Clave) */
        .kpi-container { display: flex; justify-content: space-between; gap: 15px; margin-bottom: 40px; }
        .kpi-box { 
            flex: 1; 
            text-align: center; 
            background: #fff; 
            border: 1px solid #e2e8f0; 
            border-radius: 8px; 
            padding: 15px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }
        .kpi-box::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: #C6F211; /* Acento corporativo */
        }
        .kpi-val { font-size: 32px; font-weight: 800; color: #0f172a; display: block; line-height: 1.2; }
        .kpi-label { font-size: 10px; font-weight: bold; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        
        /* Secciones de Gráficos */
        .chart-section { margin-bottom: 40px; page-break-inside: avoid; }
        .chart-title { 
            font-size: 14px; 
            font-weight: bold; 
            color: #1e293b; 
            border-bottom: 2px solid #e2e8f0; 
            margin-bottom: 15px; 
            padding-bottom: 5px; 
            text-transform: uppercase;
        }
        
        /* Simulación de Gráfico de Barras con CSS */
        .bar-row { display: flex; align-items: center; margin-bottom: 10px; font-size: 11px; }
        .bar-label { width: 180px; text-align: right; padding-right: 15px; color: #475569; font-weight: 500; }
        .bar-track { flex: 1; background: #f1f5f9; height: 18px; border-radius: 4px; overflow: hidden; }
        .bar-fill { 
            height: 100%; 
            background: #1e293b; /* Color principal oscuro */
            text-align: right; 
            color: white; 
            padding-right: 8px; 
            line-height: 18px; 
            font-size: 10px; 
            font-weight: bold;
            min-width: 25px; /* Para que siempre se vea el número si no es 0 */
        }
        
        .footer { 
            position: fixed; 
            bottom: 0; 
            left: 0; 
            width: 100%; 
            text-align: center; 
            font-size: 9px; 
            color: #94a3b8; 
            border-top: 1px solid #f1f5f9; 
            padding-top: 10px; 
            background: white;
        }

        /* Botón Imprimir */
        @media print { .no-print { display: none; } }
        .btn-print { 
            background: #0f172a; 
            color: white; 
            padding: 10px 20px; 
            border: none; 
            cursor: pointer; 
            font-weight: bold; 
            border-radius: 5px;
            font-size: 12px;
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="text-align: center; margin-bottom: 20px; padding: 15px; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
        <button onclick="window.print()" class="btn-print">🖨️ IMPRIMIR / GUARDAR PDF</button>
    </div>

    <div class="header-container">
        <table class="header-table">
            <tr>
                <td style="text-align: left;">
                    <img src="{{ public_path('images/logo.png') }}" class="logo-img" alt="SEGUSMART 24">
                </td>
                <td class="company-info">
                    <strong style="font-size: 12px; color: #000;">SEGUSMART 24, C.A.</strong><br>
                    RIF: J-50608166-0<br>
                    Av Lara CC Rio Lama 5ta Etapa Nivel Plaza Local 38-39<br>
                    Barquisimeto, Edo. Lara 3001<br>
                    contacto@segusmart24.com
                </td>
            </tr>
        </table>
    </div>

    <div class="report-title">Resumen Ejecutivo de Actividad</div>
    <div class="report-meta">
        <strong>Cliente:</strong> {{ $customer ? $customer->full_name : 'TODOS (Reporte General)' }} <br>
        <strong>Periodo:</strong> {{ $request->date_from }} al {{ $request->date_to }}
    </div>

    <div class="kpi-container">
        <div class="kpi-box">
            <span class="kpi-val">{{ $total }}</span>
            <span class="kpi-label">Eventos Totales</span>
        </div>
        <div class="kpi-box">
            <span class="kpi-val" style="color: #dc2626;">{{ $incidents }}</span>
            <span class="kpi-label">Incidentes Prioritarios</span>
        </div>
        <div class="kpi-box">
            <span class="kpi-val" style="color: #475569;">{{ $auto }}</span>
            <span class="kpi-label">Señales Automáticas</span>
        </div>
    </div>

    <div class="chart-section">
        <div class="chart-title">Distribución por Tipo de Evento (Top 10)</div>
        @foreach($topEvents as $ev)
            @php 
                $percentage = $total > 0 ? ($ev->total / $total) * 100 : 0; 
                // Color diferente para eventos de alta prioridad
                $barColor = in_array($ev->event_code, ['BA', 'FA', 'PA']) ? '#dc2626' : '#1e293b';
            @endphp
            <div class="bar-row">
                <div class="bar-label">
                    <strong>{{ $ev->event_code }}</strong> - {{ Str::limit($ev->siaCode->description ?? 'Desconocido', 25) }}
                </div>
                <div class="bar-track">
                    <div class="bar-fill" style="width: {{ $percentage }}%; background-color: {{ $barColor }};">
                        {{ $ev->total }} ({{ round($percentage, 1) }}%)
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="chart-section">
        <div class="chart-title">Tendencia de Volumen Diario</div>
        @php $maxDaily = $eventsByDay->max('total'); @endphp
        @foreach($eventsByDay as $day)
            @php $width = $maxDaily > 0 ? ($day->total / $maxDaily) * 100 : 0; @endphp
            <div class="bar-row">
                <div class="bar-label">{{ \Carbon\Carbon::parse($day->date)->format('d/m/Y') }}</div>
                <div class="bar-track">
                    <div class="bar-fill" style="width: {{ $width }}%; background-color: #64748b;">
                        {{ $day->total }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="footer">
        Documento generado el {{ now()->setTimezone('America/Caracas')->format('d/m/Y H:i') }} por el sistema Segusmart 24.
    </div>

</body>
</html>