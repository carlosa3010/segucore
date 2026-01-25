<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GpsAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $data;

    /**
     * Create a new notification instance.
     * $data = ['device' => 'Toyota', 'msg' => 'Exceso velocidad', 'type' => 'overspeed']
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        // 'database' guarda la alerta en la tabla notifications (para mostrar la campanita en el panel)
        // 'mail' envía el correo
        return ['mail', 'database']; 
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('🚨 Alerta GPS: ' . ($this->data['device'] ?? 'Dispositivo'))
                    ->greeting('Hola ' . $notifiable->first_name)
                    ->line('Se ha detectado una alerta en su unidad:')
                    ->line('**' . $this->data['msg'] . '**')
                    ->action('Ver en Mapa en Tiempo Real', url('/cliente'))
                    ->line('Gracias por confiar en Segusmart24.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [
            'device' => $this->data['device'],
            'message' => $this->data['msg'],
            'type' => $this->data['type'],
            'time' => now()->toIso8601String()
        ];
    }
}