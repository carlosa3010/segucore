<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use Illuminate\Http\Request;

class PublicReportController extends Controller
{
    /**
     * Valida la autenticidad de un reporte físico mediante QR.
     * Esta ruta debe estar protegida por el middleware 'signed'.
     */
    public function verify(Request $request, $id)
    {
        // Si el enlace ha expirado o la firma es inválida, Laravel lanza 403 automáticamente.
        // Pero por seguridad adicional, verificamos existencia.
        
        $incident = Incident::with(['alarmEvent.account.customer', 'siaCode', 'logs.user'])
            ->findOrFail($id);

        // Generamos el Hash de Seguridad Visual (para comparar con el papel)
        $securityHash = strtoupper(substr(md5($incident->id . $incident->created_at . config('app.key')), 0, 8));

        return view('public.verify_report', compact('incident', 'securityHash'));
    }
}