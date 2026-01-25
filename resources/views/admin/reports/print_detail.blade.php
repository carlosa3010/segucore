<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Informe Oficial #{{ str_pad($incident->id, 6, '0', STR_PAD_LEFT) }}</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; color: #000; line-height: 1.3; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        
        /* HEADER */
        .header-table { width: 100%; border-bottom: 2px solid #000; margin-bottom: 15px; padding-bottom: 10px; }
        .logo-img { height: 60px; width: auto; object-fit: contain; }
        .company-info { text-align: right; font-size: 10px; }

        /* TITULO */
        h1 { text-align: center; text-transform: uppercase; font-size: 18px; margin: 10px 0 20px 0; letter-spacing: 1px; }

        /* SECCIONES Y TABLAS */
        .section { margin-top: 25px; border: 1px solid #000; padding: 10px; page-break-inside: avoid; }
        .section-title { font-weight: bold; text-transform: uppercase; background: #eee; display: block; margin: -10px -10px 10px -10px; padding: 5px 10px; border-bottom: 1px solid #000; font-size: 11px; }
        
        .row { display: flex; margin-bottom: 4px; }
        .label { font-weight: bold; width: 140px; color: #333; }
        .value { flex: 1; }

        table { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 5px; }
        th, td { border: 1px solid #999; padding: 5px; text-align: left; }
        th { background-color: #f0f0f0; }
        
        /* ESTILOS DE IMPRESIÓN */
        @media print {
            body { -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
            tr { page-break-inside: avoid; }
            .page-break { page-break-before: always; }
        }

        /* SEGURIDAD */
        .security-footer { margin-top: 30px; border-top: 1px dashed #000; padding-top: 15px; display: flex; align-items: center; justify-content: space-between; page-break-inside: avoid; }
        .qr-container { width: 80px; height: 80px; }
        .hash-code { font-family: 'OCR A Std', monospace; font-size: 14px; font-weight: bold; letter-spacing: 2px; }
    </style>
</head>
<body>

    <div class="no-print" style="position: fixed; top: 0; right: 0; padding: 10px; background: #ddd; opacity: 0.9;">
        <button onclick="window.print()" style="font-size: 16px; padding: 10px 20px; cursor: pointer; font-weight: bold;">🖨️ IMPRIMIR / GUARDAR PDF</button>
    </div>

    <div class="container">
        
        <table class="header-table">
            <tr>
                <td style="border:none; width: 30%;">
                    <img src="{{ asset('images/logo-white.png') }}" alt="SEGUSMART" class="logo-img" style="filter: invert(1);"> 
                </td>
                <td style="border:none; width: 70%; text-align: right;">
                    <strong>SEGUSMART 24, C.A.</strong><br>
                    RIF: J-50462276-0<br>
                    Centro de Control y Monitoreo 24/7<br>
                    Barquisimeto, Venezuela
                </td>
            </tr>
        </table>

        <h1>Informe Técnico de Incidente #{{ str_pad($incident->id, 6, '0', STR_PAD_LEFT) }}</h1>

        <div class="section">
            <span class="section-title">Información del Cliente y Cuenta</span>
            <div class="row"><span class="label">CLIENTE:</span><span class="value">{{ $incident->alarmEvent->account->customer->full_name }}</span></div>
            <div class="row"><span class="label">ID LEGAL:</span><span class="value">{{ $incident->alarmEvent->account->customer->national_id }}</span></div>
            <div class="row"><span class="label">CUENTA:</span><span class="value"><strong>{{ $incident->alarmEvent->account_number }}</strong> - {{ $incident->alarmEvent->account->branch_name }}</span></div>
            <div class="row"><span class="label">DIRECCIÓN:</span><span class="value">{{ $incident->alarmEvent->account->installation_address }}</span></div>
        </div>

        <div class="section">
            <span class="section-title">Detalle del Evento Disparador</span>
            <div class="row">
                <span class="label">FECHA/HORA:</span>
                <span class="value"><strong>{{ $incident->created_at->setTimezone('America/Caracas')->format('d/m/Y h:i:s A') }}</strong></span>
            </div>
            <div class="row"><span class="label">CÓDIGO SIA:</span><span class="value">{{ $incident->alarmEvent->event_code }}</span></div>
            <div class="row"><span class="label">DESCRIPCIÓN:</span><span class="value">{{ $incident->alarmEvent->siaCode->description }}</span></div>
            <div class="row"><span class="label">ZONA / ÁREA:</span><span class="value">Zona {{ $incident->alarmEvent->zone }} | Partición {{ $incident->alarmEvent->partition }}</span></div>
            <div class="row"><span class="label">ORIGEN:</span><span class="value">{{ $incident->alarmEvent->ip_address ?? 'GPRS/Telefónico' }}</span></div>
        </div>

        <div class="section">
            <span class="section-title">Cronología de Atención (Bitácora)</span>
            <table>
                <thead>
                    <tr>
                        <th width="15%">Hora</th>
                        <th width="15%">Operador</th>
                        <th width="10%">Acción</th>
                        <th width="60%">Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($incident->logs as $log)
                    <tr>
                        <td>{{ $log->created_at->setTimezone('America/Caracas')->format('h:i:s A') }}</td>
                        <td>{{ $log->user->name ?? 'SISTEMA' }}</td>
                        <td>{{ $log->action_type }}</td>
                        <td>{{ $log->description }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="section">
            <span class="section-title">Cierre y Conclusión</span>
            <div class="row"><span class="label">RESULTADO:</span><span class="value" style="font-weight: bold; text-transform: uppercase;">{{ $incident->result }}</span></div>
            <div class="row">
                <span class="label">CIERRE:</span>
                <span class="value">
                    {{-- Verifica si usamos closed_at o resolved_at según tu modelo --}}
                    {{ ($incident->resolved_at ?? $incident->closed_at) 
                        ? \Carbon\Carbon::parse($incident->resolved_at ?? $incident->closed_at)->setTimezone('America/Caracas')->format('d/m/Y h:i:s A') 
                        : 'ABIERTO' 
                    }}
                </span>
            </div>
            <div style="margin-top: 10px; border: 1px solid #ccc; padding: 5px; background: #fafafa;">
                <strong>Nota Final del Operador:</strong><br>
                {{ $incident->notes ?? 'Sin notas de cierre.' }}
            </div>
        </div>

        @php
            // El hash debe generarse con los datos originales (UTC) para consistencia técnica
            $securityHash = strtoupper(substr(md5($incident->id . $incident->created_at . config('app.key')), 0, 8));
            // URL firmada
            $verificationUrl = URL::signedRoute('report.verify', ['id' => $incident->id]);
        @endphp

        <div class="security-footer">
            <div style="flex: 1;">
                <strong>VERIFICACIÓN DE AUTENTICIDAD</strong><br>
                <small>Escanee el código QR para validar la integridad de este documento físico en nuestros servidores.</small><br><br>
                SERIAL DE SEGURIDAD:<br>
                <span class="hash-code">#{{ $securityHash }}-{{ $incident->id }}</span>
            </div>
            <div id="qrcode" class="qr-container"></div>
        </div>

    </div>

    <script type="text/javascript">
        new QRCode(document.getElementById("qrcode"), {
            text: "{{ $verificationUrl }}",
            width: 80,
            height: 80,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.M
        });
    </script>

</body>
</html>