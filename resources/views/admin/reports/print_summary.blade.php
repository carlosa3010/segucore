<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de Actividad - {{ $customer->full_name }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .logo { font-size: 20px; font-weight: bold; }
        .info-box { margin-bottom: 20px; padding: 10px; border: 1px solid #ccc; background: #f9f9f9; }
        .stats-grid { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .stat { text-align: center; border: 1px solid #eee; padding: 10px; flex: 1; margin: 0 5px; }
        .stat-val { font-size: 18px; font-weight: bold; display: block; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        .footer { text-align: center; font-size: 10px; margin-top: 30px; color: #666; border-top: 1px solid #ccc; padding-top: 10px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="background: #333; color: #fff; padding: 10px; text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-weight: bold; cursor: pointer;">🖨️ IMPRIMIR REPORTE</button>
    </div>

    <div class="header">
        <div class="logo">SEGUSMART 24 C.A.</div>
        <div>Reporte de Actividad de Alarmas</div>
        <small>Generado el: {{ now()->format('d/m/Y H:i') }}</small>
    </div>

    <div class="info-box">
        <strong>Cliente:</strong> {{ $customer->business_name ?? $customer->full_name }}<br>
        <strong>Periodo:</strong> {{ \Carbon\Carbon::parse(request('date_from'))->format('d/m/Y') }} al {{ \Carbon\Carbon::parse(request('date_to'))->format('d/m/Y') }}<br>
        <strong>Cuentas Asociadas:</strong> {{ $customer->accounts->pluck('account_number')->implode(', ') }}
    </div>

    <div class="stats-grid">
        <div class="stat"><span class="stat-val">{{ $stats['total'] }}</span>Señales Recibidas</div>
        <div class="stat"><span class="stat-val">{{ $stats['incidents'] }}</span>Gestionadas</div>
        <div class="stat"><span class="stat-val">{{ $stats['real_alarms'] }}</span>Eventos Reales</div>
        <div class="stat"><span class="stat-val">{{ $stats['false_alarms'] }}</span>Falsas Alarmas</div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="15%">Fecha / Hora</th>
                <th width="10%">Abonado</th>
                <th width="10%">Cód</th>
                <th width="35%">Descripción Evento</th>
                <th width="10%">Zona</th>
                <th width="20%">Resolución / Notas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($events as $e)
            <tr>
                <td>{{ $e->created_at->format('d/m/Y H:i:s') }}</td>
                <td>{{ $e->account_number }}</td>
                <td>{{ $e->event_code }}</td>
                <td>{{ $e->siaCode->description ?? 'Evento desconocido' }}</td>
                <td>{{ $e->zone }}</td>
                <td>
                    @if($e->incident)
                        @if($e->incident->result == 'false_alarm') <strong style="color:red">[Falsa]</strong>
                        @elseif(str_contains($e->incident->result, 'real')) <strong style="color:darkgreen">[REAL]</strong>
                        @endif
                        {{ Str::limit($e->incident->notes, 50) }}
                    @else
                        <span style="color:#999; font-style:italic;">Automático</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Este documento es un reporte generado electrónicamente por el sistema Segusmart Core.
    </div>

</body>
</html>