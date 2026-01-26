<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Historial - {{ $device->name }}</title>
    <style>
        body { 
            font-family: sans-serif; 
            font-size: 12px; 
            color: #333;
        }
        
        /* Encabezado Corporativo */
        .header { 
            width: 100%; 
            border-bottom: 2px solid #C6F211; 
            padding-bottom: 10px; 
            margin-bottom: 20px; 
        }
        .logo { 
            float: left; 
            width: 180px; 
            padding-top: 5px; 
        }
        .company-info { 
            float: right; 
            text-align: right; 
            font-size: 10px; 
            color: #555; 
            line-height: 1.3;
        }
        
        .clearfix::after { 
            content: ""; 
            clear: both; 
            display: table; 
        }

        .report-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .info-box { 
            background-color: #f8fafc;
            border: 1px solid #e2e8f0; 
            padding: 15px; 
            margin-bottom: 20px; 
            border-radius: 5px;
            font-size: 11px;
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
            font-size: 11px;
        }
        th, td { 
            border-bottom: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
        }
        th { 
            background-color: #000; 
            color: #fff; 
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .alert { 
            color: #d32f2f; 
            font-weight: bold; 
        }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            color: white;
            display: inline-block;
        }
        .bg-red { background-color: #ef4444; }
        .bg-green { background-color: #22c55e; }
        .bg-gray { background-color: #64748b; }
    </style>
</head>
<body>
    
    <div class="header clearfix">
        <div class="logo">
            <img src="{{ public_path('images/logo.png') }}" style="height: 55px;"> 
        </div>
        <div class="company-info">
            <strong style="font-size: 12px; color: #000;">Segusmart 24, C.A.</strong><br>
            RIF: J-50608166-0<br>
            Av Lara CC Rio Lama 5ta Etapa Nivel Plaza Local 38-39<br>
            Barquisimeto Edo Lara 3001<br>
            contacto@segusmart24.com | +58 412-1405670<br>
            <br>
            <em>Generado: {{ now()->format('d/m/Y H:i') }}</em>
        </div>
    </div>

    <div class="report-title">Reporte Detallado de Recorrido</div>

    <div class="info-box">
        <table style="border: none; margin: 0;">
            <tr style="background: transparent;">
                <td style="border: none; padding: 2px;"><strong>Vehículo:</strong> {{ $device->name }}</td>
                <td style="border: none; padding: 2px;"><strong>Placa/IMEI:</strong> {{ $device->plate_number ?? $device->imei }}</td>
            </tr>
            <tr style="background: transparent;">
                <td style="border: none; padding: 2px;"><strong>Cliente:</strong> {{ $device->customer->business_name ?? $device->customer->full_name }}</td>
                <td style="border: none; padding: 2px;"><strong>Total Registros:</strong> {{ $positions->count() }}</td>
            </tr>
            <tr style="background: transparent;">
                <td style="border: none; padding: 2px;" colspan="2">
                    <strong>Rango de Fecha:</strong> 
                    {{ $from->format('d/m/Y H:i') }} - {{ $to->format('d/m/Y H:i') }}
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha / Hora</th>
                <th>Velocidad</th>
                <th>Coordenadas</th>
                <th>Evento / Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($positions as $pos)
            @php 
                $speed = round($pos->speed * 1.852); 
                $date = \Carbon\Carbon::parse($pos->fixtime)->setTimezone('America/Caracas');
                $limit = $device->speed_limit ?? 80;
                $isOverspeed = $speed > $limit;
            @endphp
            <tr>
                <td>{{ $date->format('d/m/Y H:i:s') }}</td>
                <td>
                    <span class="{{ $isOverspeed ? 'alert' : '' }}">
                        {{ $speed }} km/h
                    </span>
                </td>
                <td style="font-family: monospace; font-size: 10px;">
                    {{ number_format($pos->latitude, 5) }}, {{ number_format($pos->longitude, 5) }}
                </td>
                <td>
                    @if($speed == 0) 
                        <span class="badge bg-gray">DETENIDO</span>
                    @elseif($isOverspeed) 
                        <span class="badge bg-red">EXCESO VELOCIDAD</span>
                    @else 
                        <span class="badge bg-green">EN MOVIMIENTO</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div style="margin-top: 30px; font-size: 9px; text-align: center; color: #999; border-top: 1px solid #eee; padding-top: 10px;">
        Este documento es un reporte generado automáticamente por el sistema Segusmart 24.
    </div>

</body>
</html>