<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Factura #{{ $invoice->invoice_number }}</title>
    <style>
        body { 
            font-family: sans-serif; 
            color: #333; 
            font-size: 14px;
            line-height: 1.6;
        }
        .header { 
            border-bottom: 2px solid #C6F211; 
            padding-bottom: 20px; 
            margin-bottom: 30px; 
        }
        .logo {
            max-width: 180px;
            height: auto;
            margin-bottom: 10px;
        }
        .company-info {
            text-align: right;
            font-size: 12px;
            color: #555;
        }
        .invoice-title { 
            font-size: 28px; 
            font-weight: bold; 
            color: #0f172a; 
            margin: 0;
            text-transform: uppercase;
        }
        .invoice-meta {
            margin-bottom: 40px;
        }
        .details-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
        }
        .details-table th { 
            background: #f1f5f9; 
            text-align: left; 
            padding: 12px; 
            border-bottom: 2px solid #e2e8f0;
            font-weight: bold;
            color: #1e293b;
        }
        .details-table td { 
            padding: 12px; 
            border-bottom: 1px solid #eee; 
        }
        .total-section { 
            margin-top: 30px; 
            text-align: right; 
            font-size: 20px; 
            font-weight: bold;
            color: #000;
        }
        .footer-note {
            margin-top: 60px;
            padding: 15px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            text-align: center;
            font-size: 11px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="header">
        <table width="100%">
            <tr>
                <td valign="top">
                    {{-- Logo desde public/images/logo.png --}}
                    <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Segusmart 24">
                </td>
                <td valign="top" class="company-info">
                    <strong style="font-size: 16px; color: #000;">Segusmart 24, C.A.</strong><br>
                    <strong>RIF:</strong> J-50608166-0<br>
                    Av Lara CC Rio Lama 5ta Etapa<br>
                    Nivel Plaza Local 38-39<br>
                    Barquisimeto Edo Lara 3001<br>
                    contacto@segusmart24.com<br>
                    +58 412-1405670
                </td>
            </tr>
        </table>
    </div>

    <div class="invoice-meta">
        <table width="100%">
            <tr>
                <td width="60%" valign="top">
                    <strong style="color: #64748b; text-transform: uppercase; font-size: 10px;">Facturar a:</strong><br>
                    <strong style="font-size: 16px;">{{ $invoice->customer->full_name }}</strong><br>
                    <span style="font-size: 12px;">ID / RIF: {{ $invoice->customer->national_id }}</span><br>
                    <span style="font-size: 12px;">{{ $invoice->customer->address ?? $invoice->customer->city }}</span>
                </td>
                <td width="40%" valign="top" style="text-align: right;">
                    <h1 class="invoice-title">FACTURA</h1>
                    <div style="margin-top: 5px;">
                        <strong style="color: #d32f2f; font-size: 16px;">#{{ $invoice->invoice_number }}</strong>
                    </div>
                    <div style="margin-top: 5px; font-size: 12px;">
                        <strong>Fecha Emisión:</strong> {{ $invoice->issue_date->format('d/m/Y') }}<br>
                        <strong>Vencimiento:</strong> {{ $invoice->due_date->format('d/m/Y') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="details-table">
        <thead>
            <tr>
                <th>Descripción</th>
                <th width="10%" style="text-align: center;">Cant.</th>
                <th width="20%" style="text-align: right;">Precio Unit.</th>
                <th width="20%" style="text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Plan de Servicio Base</strong><br>
                    <span style="font-size: 11px; color: #666;">{{ $invoice->details['plan_name'] ?? 'Servicio de Monitoreo y Seguridad' }}</span>
                </td>
                <td align="center">1</td>
                <td align="right">${{ number_format($invoice->details['base_price'], 2) }}</td>
                <td align="right">${{ number_format($invoice->details['base_price'], 2) }}</td>
            </tr>

            @if(isset($invoice->details['gps_qty']) && $invoice->details['gps_qty'] > 0)
            <tr>
                <td>Monitoreo GPS Activo</td>
                <td align="center">{{ $invoice->details['gps_qty'] }}</td>
                <td align="right">${{ number_format($invoice->details['gps_rate'], 2) }}</td>
                <td align="right">${{ number_format($invoice->details['gps_qty'] * $invoice->details['gps_rate'], 2) }}</td>
            </tr>
            @endif

            @if(isset($invoice->details['alarm_qty']) && $invoice->details['alarm_qty'] > 0)
            <tr>
                <td>Monitoreo Alarma Activo</td>
                <td align="center">{{ $invoice->details['alarm_qty'] }}</td>
                <td align="right">${{ number_format($invoice->details['alarm_rate'], 2) }}</td>
                <td align="right">${{ number_format($invoice->details['alarm_qty'] * $invoice->details['alarm_rate'], 2) }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="total-section">
        TOTAL A PAGAR: ${{ number_format($invoice->total, 2) }}
    </div>

    <div class="footer-note">
        <strong>NOTA IMPORTANTE:</strong><br>
        En caso de requerir <strong>Factura Fiscal</strong>, se debe sumar el <strong>IVA (16%)</strong> al monto total reflejado en este documento.
    </div>
</body>
</html>