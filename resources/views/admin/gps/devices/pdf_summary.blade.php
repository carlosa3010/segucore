<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte Resumido - {{ $device->name }}</title>
    <style>
        body { font-family: sans-serif; color: #333; }
        .header { width: 100%; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { float: left; width: 150px; }
        .company-info { float: right; text-align: right; font-size: 12px; }
        .title { text-align: center; font-size: 18px; font-weight: bold; margin-top: 30px; text-transform: uppercase; }
        
        .card-container { width: 100%; margin-top: 20px; }
        .metric-box {
            float: left; width: 30%; background: #f4f4f4; 
            margin-right: 3%; padding: 15px; border-radius: 5px; text-align: center;
            margin-bottom: 15px;
        }
        .metric-title { font-size: 10px; text-transform: uppercase; color: #666; margin-bottom: 5px; }
        .metric-value { font-size: 18px; font-weight: bold; color: #000; }
        
        .section-title { font-size: 14px; font-weight: bold; border-bottom: 1px solid #ccc; margin: 20px 0 10px 0; padding-bottom: 5px; }
        
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 12px; }
        .table td { padding: 8px; border-bottom: 1px solid #eee; }
        .table th { padding: 8px; background: #000; color: #fff; text-align: left; }
        
        .clearfix::after { content: ""; clear: both; display: table; }
    </style>
</head>
<body>

    <div class="header clearfix">
        <div class="logo">
            <img src="{{ public_path('images/logo-white.png') }}" style="height: 40px; filter: invert(1);"> 
        </div>
        <div class="company-info">
            <strong>SeguCore Admin</strong><br>
            Generado: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <div class="title">Reporte Ejecutivo de Flota</div>
    
    <div style="text-align: center; font-size: 12px; margin-bottom: 20px;">
        Unidad: <strong>{{ $device->name }}</strong> ({{ $device->plate_number ?? 'S/P' }})<br>
        Cliente: {{ $device->customer->business_name ?? $device->customer->full_name }}<br>
        Desde: {{ $start->format('d/m/Y H:i') }} &nbsp;|&nbsp; Hasta: {{ $end->format('d/m/Y H:i') }}
    </div>

    <div class="section-title">Resumen de Operación</div>
    
    <div class="card-container clearfix">
        <div class="metric-box">
            <div class="metric-title">Distancia Recorrida</div>
            <div class="metric-value">{{ $stats['distance'] }} km</div>
        </div>
        <div class="metric-box">
            <div class="metric-title">Tiempo Motor Encendido</div>
            <div class="metric-value">{{ $stats['total_engine_str'] }}</div>
        </div>
        <div class="metric-box" style="margin-right: 0;">
            <div class="metric-title">Tiempo Apagado</div>
            <div class="metric-value">{{ $stats['off_str'] }}</div>
        </div>
    </div>

    <div class="card-container clearfix">
        <div class="metric-box">
            <div class="metric-title">En Movimiento</div>
            <div class="metric-value">{{ $stats['move_str'] }}</div>
        </div>
        <div class="metric-box">
            <div class="metric-title">En Ralentí (Detenido ON)</div>
            <div class="metric-value">{{ $stats['stop_str'] }}</div>
        </div>
        <div class="metric-box" style="margin-right: 0;">
            <div class="metric-title">Velocidad Máxima</div>
            <div class="metric-value">{{ $stats['max_speed'] }} km/h</div>
        </div>
    </div>

    <div class="section-title">Detalles del Vehículo</div>
    <table class="table">
        <tr>
            <th>Conductor Asignado</th>
            <td>{{ optional($device->driver)->first_name ?? 'Sin asignar' }} {{ optional($device->driver)->last_name }}</td>
        </tr>
        <tr>
            <th>IMEI</th>
            <td>{{ $device->imei }}</td>
        </tr>
        <tr>
            <th>Marca / Modelo</th>
            <td>{{ $device->model ?? 'Genérico' }}</td>
        </tr>
    </table>

    <div style="margin-top: 50px; font-size: 10px; text-align: center; color: #999;">
        Este documento es un reporte administrativo generado por SeguCore.
    </div>

</body>
</html>