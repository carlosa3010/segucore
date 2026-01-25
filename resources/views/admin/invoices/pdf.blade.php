<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; color: #333; }
        .header { border-bottom: 2px solid #C6F211; padding-bottom: 10px; }
        .invoice-title { font-size: 24px; font-bold; color: #0f172a; }
        .details-table { w-100; border-collapse: collapse; margin-top: 20px; }
        .details-table th { background: #f1f5f9; text-align: left; padding: 10px; }
        .details-table td { padding: 10px; border-bottom: 1px solid #eee; }
        .total-section { margin-top: 30px; text-align: right; font-size: 18px; }
    </style>
</head>
<body>
    <div class="header">
        <table width="100%">
            <tr>
                <td><h1 class="invoice-title">FACTURA</h1></td>
                <td align="right text-sm">
                    <strong>SeguSmart 24</strong><br>
                    RIF: J-12345678-9<br>
                    {{ date('d/m/Y') }}
                </td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 20px;">
        <strong>Cliente:</strong> {{ $invoice->customer->full_name }}<br>
        <strong>ID:</strong> {{ $invoice->customer->national_id }}<br>
        <strong>Factura Nro:</strong> {{ $invoice->invoice_number }}
    </div>

    <table class="details-table" width="100%">
        <thead>
            <tr>
                <th>Descripción</th>
                <th>Cant.</th>
                <th>Precio Unit.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Plan de Servicio Base ({{ $invoice->details['plan_name'] ?? 'Plan' }})</td>
                <td>1</td>
                <td>${{ number_format($invoice->details['base_price'], 2) }}</td>
                <td>${{ number_format($invoice->details['base_price'], 2) }}</td>
            </tr>
            @if($invoice->details['gps_qty'] > 0)
            <tr>
                <td>Monitoreo GPS Activo</td>
                <td>{{ $invoice->details['gps_qty'] }}</td>
                <td>${{ number_format($invoice->details['gps_rate'], 2) }}</td>
                <td>${{ number_format($invoice->details['gps_qty'] * $invoice->details['gps_rate'], 2) }}</td>
            </tr>
            @endif
            @if($invoice->details['alarm_qty'] > 0)
            <tr>
                <td>Monitoreo Alarma Activo</td>
                <td>{{ $invoice->details['alarm_qty'] }}</td>
                <td>${{ number_format($invoice->details['alarm_rate'], 2) }}</td>
                <td>${{ number_format($invoice->details['alarm_qty'] * $invoice->details['alarm_rate'], 2) }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="total-section">
        <strong>TOTAL A PAGAR: ${{ number_format($invoice->total, 2) }}</strong>
    </div>
</body>
</html>