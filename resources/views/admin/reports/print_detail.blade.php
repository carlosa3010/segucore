<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Informe de Incidente #{{ $incident->id }}</title>
    <style>
        body { font-family: monospace; font-size: 13px; color: #000; }
        .container { max-width: 800px; margin: 0 auto; border: 1px solid #000; padding: 20px; }
        h1 { text-align: center; text-transform: uppercase; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .section { margin-top: 20px; border-top: 1px dashed #666; padding-top: 10px; }
        .section-title { font-weight: bold; text-transform: uppercase; background: #eee; padding: 5px; display: block; margin-bottom: 10px; }
        .row { display: flex; margin-bottom: 5px; }
        .label { font-weight: bold; width: 150px; }
        .value { flex: 1; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 5px; }
        th, td { border: 1px solid #999; padding: 4px; text-align: left; }
        .history-item { color: #666; font-size: 10px; }
    </style>
</head>
<body onload="window.print()">

    <div class="container">
        <h1>Informe de Incidente #{{ str_pad($incident->id, 6, '0', STR_PAD_LEFT) }}</h1>

        <div class="row">
            <span class="label">FECHA/HORA:</span>
            <span class="value">{{ $incident->created_at->format('d/m/Y H:i:s') }}</span>
        </div>
        <div class="row">
            <span class="label">ABONADO:</span>
            <span class="value"><strong>{{ $incident->alarmEvent->account_number }}</strong> - {{ $incident->alarmEvent->account->branch_name }}</span>
        </div>
        <div class="row">
            <span class="label">CLIENTE:</span>
            <span class="value">{{ $incident->alarmEvent->account->customer->full_name }} ({{ $incident->alarmEvent->account->customer->national_id }})</span>
        </div>
        <div class="row">
            <span class="label">DIRECCIÓN:</span>
            <span class="value">{{ $incident->alarmEvent->account->installation_address }}</span>
        </div>

        <div class="section">
            <span class="section-title">Detalle de la Señal</span>
            <div class="row"><span class="label">CÓDIGO SIA:</span><span class="value">{{ $incident->alarmEvent->event_code }}</span></div>
            <div class="row"><span class="label">DESCRIPCIÓN:</span><span class="value"><strong>{{ $incident->alarmEvent->siaCode->description }}</strong></span></div>
            <div class="row"><span class="label">ZONA:</span><span class="value">{{ $incident->alarmEvent->zone }} ({{ $incident->alarmEvent->zone_name ?? 'No definida' }})</span></div>
            <div class="row"><span class="label">TRAMA RAW:</span><span class="value" style="font-size: 10px;">{{ $incident->alarmEvent->raw_data }}</span></div>
        </div>

        <div class="section">
            <span class="section-title">Bitácora de Gestión (Cronología)</span>
            <table>
                <thead>
                    <tr>
                        <th width="15%">Hora</th>
                        <th width="15%">Operador</th>
                        <th width="10%">Tipo</th>
                        <th width="60%">Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($incident->logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('H:i:s') }}</td>
                        <td>{{ $log->user->name ?? 'SISTEMA' }}</td>
                        <td>{{ $log->action_type }}</td>
                        <td>{{ $log->description }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="section">
            <span class="section-title">Cierre y Resolución</span>
            <div class="row">
                <span class="label">RESULTADO:</span>
                <span class="value" style="text-transform: uppercase;">{{ $incident->result }}</span>
            </div>
            <div class="row">
                <span class="label">INFORME FINAL:</span>
                <span class="value" style="border: 1px solid #ccc; padding: 5px; display: block;">
                    {{ $incident->notes }}
                </span>
            </div>
            <div class="row" style="margin-top: 10px;">
                <span class="label">CERRADO POR:</span>
                <span class="value">{{ $incident->operator->name ?? 'N/A' }} a las {{ $incident->closed_at ? $incident->closed_at->format('d/m/Y H:i') : '---' }}</span>
            </div>
        </div>

        <div class="section">
            <span class="section-title">Contexto (Últimos 15 Eventos Previos)</span>
            <table>
                @foreach($history as $h)
                <tr class="history-item">
                    <td width="20%">{{ $h->created_at->format('d/m H:i') }}</td>
                    <td width="10%">{{ $h->event_code }}</td>
                    <td>{{ $h->siaCode->description ?? '' }}</td>
                    <td width="10%">Z:{{ $h->zone }}</td>
                </tr>
                @endforeach
            </table>
        </div>

        <div style="text-align: center; margin-top: 40px; font-size: 10px;">
            __________________________<br>
            Firma Supervisor
        </div>
    </div>

</body>
</html>