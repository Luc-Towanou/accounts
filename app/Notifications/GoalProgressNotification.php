<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class GoalProgressNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $goal;
    protected $threshold;

    public function __construct($goal, $threshold)
    {
        $this->goal = $goal;
        $this->threshold = $threshold;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Progrès de votre objectif')
            ->line("Félicitations ! Vous avez atteint {$this->threshold}% de votre objectif : {$this->goal->title}.")
            ->action('Voir votre objectif', url('/goals/' . $this->goal->id))
            ->line('Continuez votre bon travail !');
    }

    public function toArray($notifiable)
    {
        return [
            'goal_id' => $this->goal->id,
            'threshold' => $this->threshold,
        ];
    }
}