<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GpsDevice;
use App\Models\TraccarDevice;
use App\Models\DeviceAlert;
use Carbon\Carbon;

class CheckGpsAlerts extends Command
{
    protected $signature = 'gps:check-alerts';
    protected $description = 'Verifica reglas de alertas (Velocidad, etc)';

    public function handle()
    {
        $devices = GpsDevice::where('subscription_status', 'active')->get();

        foreach ($devices as $dev) {
            // Obtener última posición de Traccar
            $traccarDev = TraccarDevice::where('uniqueid', $dev->imei)->with('position')->first();
            
            if (!$traccarDev || !$traccarDev->position) continue;

            $speedKmh = round($traccarDev->position->speed * 1.852); // Nudos a Km/h

            // 1. REGLA: Exceso de Velocidad
            if ($dev->speed_limit > 0 && $speedKmh > $dev->speed_limit) {
                $this->triggerAlert($dev, 'overspeed', "Exceso de velocidad: {$speedKmh} km/h (Límite: {$dev->speed_limit})", [
                    'speed' => $speedKmh,
                    'lat' => $traccarDev->position->latitude,
                    'lng' => $traccarDev->position->longitude
                ]);
            }
        }
    }

    private function triggerAlert($device, $type, $message, $data)
    {
        // Evitar spam: No crear alerta si ya existe una igual en los últimos 10 min
        $exists = DeviceAlert::where('gps_device_id', $device->id)
            ->where('type', $type)
            ->where('created_at', '>=', Carbon::now()->subMinutes(10))
            ->exists();

        if (!$exists) {
            DeviceAlert::create([
                'gps_device_id' => $device->id,
                'type' => $type,
                'message' => $message,
                'data' => $data
            ]);
            
            // AQUÍ INTEGRARÁS WHATSAPP/SMS A FUTURO:
            // $device->customer->notify(new \App\Notifications\GpsAlertNotification($message));
            
            $this->info("Alerta creada para {$device->name}: {$message}");
        }
    }
}