<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GpsDevice;
use App\Models\TraccarDevice;
use App\Models\DeviceAlert;
use App\Notifications\GpsAlertNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckGpsAlerts extends Command
{
    protected $signature = 'gps:check-alerts';
    protected $description = 'Sincroniza posiciones y verifica reglas de alertas (Velocidad, Batería, etc)';

    public function handle()
    {
        // 1. Obtener dispositivos activos
        $devices = GpsDevice::where('is_active', true)->get();

        $count = 0;

        foreach ($devices as $dev) {
            // Obtener datos en vivo de Traccar
            $traccarDev = TraccarDevice::where('uniqueid', $dev->imei)->with('position')->first();
            
            if (!$traccarDev || !$traccarDev->position) continue;

            $pos = $traccarDev->position;
            
            // Conversión correcta de Nudos a Km/h
            $speedKmh = round($pos->speed * 1.852); 
            
            // Manejo seguro de atributos (JSON o Array)
            $attributes = is_array($pos->attributes) 
                ? $pos->attributes 
                : (json_decode($pos->attributes, true) ?? []);
            
            $batteryLevel = $attributes['batteryLevel'] ?? $attributes['battery'] ?? null;
            $ignition = $attributes['ignition'] ?? false;

            // 2. SINCRONIZACIÓN LOCAL
            $dev->update([
                'last_latitude'  => $pos->latitude,
                'last_longitude' => $pos->longitude,
                'speed'          => $speedKmh,
                'battery_level'  => $batteryLevel,
                // Lógica de estado: Si se mueve y tiene ignición = online, sino stopped
                'status'         => ($speedKmh > 2 && $ignition) ? 'online' : 'stopped',
                'updated_at'     => now() 
            ]);

            // 3. REGLAS DE ALERTAS
            
            // A. Exceso de Velocidad
            // CORRECCIÓN: Usamos $dev (no $device) y $speedKmh (ya calculado)
            if ($dev->speed_limit > 0 && $speedKmh > $dev->speed_limit) {
                $this->triggerAlert(
                    $dev, 
                    'overspeed', 
                    "⚠️ Exceso de velocidad: {$speedKmh} km/h (Límite: {$dev->speed_limit} km/h)", 
                    [
                        'speed' => $speedKmh,
                        'lat' => $pos->latitude,
                        'lng' => $pos->longitude
                    ]
                );
            }

            // B. Batería Baja
            if ($batteryLevel !== null && $batteryLevel < 20) {
                 $this->triggerAlert(
                    $dev, 
                    'low_battery', 
                    "🔋 Batería baja: {$batteryLevel}%", 
                    ['battery' => $batteryLevel]
                );
            }

            $count++;
        }

        $this->info("Sincronizados {$count} dispositivos activos.");
    }

    private function triggerAlert($device, $type, $message, $data)
    {
        // Evitar spam: 15 minutos de "silencio" entre alertas iguales
        $exists = DeviceAlert::where('gps_device_id', $device->id)
            ->where('type', $type)
            ->where('created_at', '>=', Carbon::now()->subMinutes(15))
            ->exists();

        if (!$exists) {
            DeviceAlert::create([
                'gps_device_id' => $device->id,
                'type' => $type,
                'message' => $message,
                'data' => $data // Laravel casteará esto a JSON automáticamente si está en $casts
            ]);
            
            if ($device->customer) {
                try {
                    $device->customer->notify(new GpsAlertNotification([
                        'device' => $device->name,
                        'type' => $type,
                        'msg' => $message
                    ]));
                } catch (\Exception $e) {
                    Log::error("Error enviando notificación GPS: " . $e->getMessage());
                }
            }
        }
    }
}