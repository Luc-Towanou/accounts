<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConfirmationInscription extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $user;
    public function __construct($user)
    {
        //
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Inscription finalisé.')
                    ->greeting('Bonjour ' . $notifiable->nom) 
                    ->line('Votre inscription est finalisée avec succes.')
                    ->action('Vous pouvez vous connecter ici:', url('http://localhost:8001/api/auth/login'))
                    ->line('Thank you for using our application!')
                    ->salutation('Cordialement, L\'équipe Support');
    }

    public function render(): string
    {
        $frontUrl = config('front.front_url'); //cacala omon___

        return "Bonjour,<br><br>
                Votre inscription est finalisée avec succes.<br><br>
                Vous pouvez vous connecter ici: <a href= '{$frontUrl}' >{$frontUrl}</a><br><br>";
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
