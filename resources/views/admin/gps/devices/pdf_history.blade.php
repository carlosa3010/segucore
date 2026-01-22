<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Historial</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .info-box { border: 1px solid #ccc; padding: 10px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .alert { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Reporte de Recorrido GPS</h2>
        <p>SeguSmart 24 C.A.</p>
    </div>

    <div class="info-box">
        <strong>Vehículo:</strong> {{ $device->name }} ({{ $device->plate_number }})<br>
        <strong>Cliente:</strong> {{ $device->customer->business_name ?? $device->customer->full_name }}<br>
        <strong>Rango de Fecha:</strong> {{ $from->format('d/m/Y H:i') }} - {{ $to->format('d/m/Y H:i') }}<br>
        <strong>Total Registros:</strong> {{ $positions->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha / Hora</th>
                <th>Velocidad</th>
                <th>Latitud</th>
                <th>Longitud</th>
                <th>Evento</th>
            </tr>
        </thead>
        <tbody>
            @foreach($positions as $pos)
            @php 
                $speed = round($pos->speed * 1.852); 
                $date = \Carbon\Carbon::parse($pos->fixtime)->setTimezone('America/Caracas');
            @endphp
            <tr>
                <td>{{ $date->format('d/m/Y H:i:s') }}</td>
                <td class="{{ $speed > ($device->speed_limit ?? 80) ? 'alert' : '' }}">
                    {{ $speed }} km/h
                </td>
                <td>{{ number_format($pos->latitude, 5) }}</td>
                <td>{{ number_format($pos->longitude, 5) }}</td>
                <td>
                    @if($speed == 0) Detenido
                    @elseif($speed > ($device->speed_limit ?? 80)) ⚠️ Exceso Vel.
                    @else En Movimiento @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>