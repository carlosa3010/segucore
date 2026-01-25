<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GpsDevice;
use App\Models\TraccarDevice;
use App\Models\DeviceAlert;
use App\Notifications\GpsAlertNotification; // Asegúrate de importar esto
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckGpsAlerts extends Command
{
    protected $signature = 'gps:check-alerts';
    protected $description = 'Sincroniza posiciones y verifica reglas de alertas (Velocidad, Batería, etc)';

    public function handle()
    {
        // Solo procesar dispositivos activos
        $devices = GpsDevice::where('subscription_status', 'active')->get();

        $count = 0;

        foreach ($devices as $dev) {
            // 1. Obtener datos en vivo de Traccar (Directo a BD para rapidez)
            $traccarDev = TraccarDevice::where('uniqueid', $dev->imei)->with('position')->first();
            
            // Si no hay datos en Traccar, saltar
            if (!$traccarDev || !$traccarDev->position) continue;

            $pos = $traccarDev->position;
            $speedKmh = round($pos->speed * 1.852); // Nudos a Km/h
            
            // Interpretación de atributos (Batería, Ignición, etc.)
            $attributes = json_decode($pos->attributes, true) ?? [];
            $batteryLevel = $attributes['batteryLevel'] ?? $attributes['battery'] ?? null;
            $ignition = $attributes['ignition'] ?? false;

            // ==========================================================
            // 2. SINCRONIZACIÓN (Vital para que el Panel Cliente se mueva)
            // ==========================================================
            $dev->update([
                'last_latitude'  => $pos->latitude,
                'last_longitude' => $pos->longitude,
                'speed'          => $speedKmh,
                'battery_level'  => $batteryLevel,
                // Calculamos estado: Si velocidad > 2 y encendido -> moving, sino -> stopped
                'status'         => ($speedKmh > 2 && $ignition) ? 'online' : 'stopped',
                'updated_at'     => now() 
            ]);

            // ==========================================================
            // 3. REGLAS DE ALERTAS
            // ==========================================================

            // A. Exceso de Velocidad
            if ($dev->speed_limit > 0 && $speedKmh > $dev->speed_limit) {
                $this->triggerAlert($dev, 'overspeed', "⚠️ Exceso de velocidad detectado: {$speedKmh} km/h (Límite: {$dev->speed_limit} km/h)", [
                    'speed' => $speedKmh,
                    'lat' => $pos->latitude,
                    'lng' => $pos->longitude,
                    'map_link' => "https://maps.google.com/?q={$pos->latitude},{$pos->longitude}"
                ]);
            }

            // B. Batería Baja (Ejemplo: Menor a 20%)
            if ($batteryLevel !== null && $batteryLevel < 20) {
                 $this->triggerAlert($dev, 'low_battery', "🔋 Batería baja del dispositivo: {$batteryLevel}%", [
                    'battery' => $batteryLevel
                ]);
            }

            $count++;
        }

        $this->info("Sincronizados y verificados {$count} dispositivos.");
    }

    private function triggerAlert($device, $type, $message, $data)
    {
        // Evitar spam: No crear alerta si ya existe una del MISMO TIPO en los últimos 15 min
        $exists = DeviceAlert::where('gps_device_id', $device->id)
            ->where('type', $type)
            ->where('created_at', '>=', Carbon::now()->subMinutes(15))
            ->exists();

        if (!$exists) {
            // 1. Guardar en Base de Datos
            DeviceAlert::create([
                'gps_device_id' => $device->id,
                'type' => $type,
                'message' => $message,
                'data' => $data
            ]);
            
            // 2. Enviar Notificación al Cliente (Email / Database / WhatsApp futuro)
            if ($device->customer) {
                try {
                    $device->customer->notify(new GpsAlertNotification([
                        'device' => $device->name,
                        'type' => $type,
                        'msg' => $message,
                        'time' => now()
                    ]));
                    $this->info("Notificación enviada a cliente: {$device->customer->email}");
                } catch (\Exception $e) {
                    Log::error("Error enviando notificación GPS: " . $e->getMessage());
                }
            }
            
            $this->info("Alerta generada para {$device->name}: {$message}");
        }
    }
}