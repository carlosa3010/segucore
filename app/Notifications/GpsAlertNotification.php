<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GpsAlertNotification extends Notification
{
    use Queueable;

    protected $alertData;

    public function __construct($alertData)
    {
        // $alertData espera: ['device' => 'Nombre', 'type' => 'overspeed', 'msg' => '...']
        $this->alertData = $alertData;
    }

    public function via($notifiable)
    {
        // Aquí activas 'mail', y a futuro 'whatsapp' (vía Twilio o similar)
        return ['mail', 'database']; 
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('🚨 Alerta de GPS: ' . $this->alertData['device'])
                    ->greeting('Hola ' . $notifiable->name)
                    ->line($this->alertData['msg'])
                    ->action('Ver en Mapa', url('/cliente'))
                    ->line('Hora del evento: ' . now()->format('H:i:s'));
    }

    public function toArray($notifiable)
    {
        return [
            'message' => $this->alertData['msg'],
            'type' => $this->alertData['type'],
            'device' => $this->alertData['device']
        ];
    }
}