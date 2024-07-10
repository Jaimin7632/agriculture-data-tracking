<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SensorAlertEmail extends Mailable
{
    use Queueable;

    public $user;
    public $sensorName;
    public $minValue;
    public $maxValue;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user="", $sensorName="", $minValue="", $maxValue="")
    {
        $this->user = $user;
        $this->sensorName = $sensorName;
        $this->minValue = $minValue;
        $this->maxValue = $maxValue;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */

    public function build()
    {
        return $this->subject('Sensor Alert')->view('emails.sensor_alert');
    }
    
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Sensor Alert')
            ->line('Hello ' . $this->user->name . ',')
            ->line('The sensor value for ' . $this->sensorName . ' has exceeded the defined thresholds:')
            ->line('Minimum Value: ' . $this->minValue)
            ->line('Maximum Value: ' . $this->maxValue)
            ->line('Please take necessary action.')
            ->line('Thank you!')
            ->salutation('Regards, Your Application');
    }

    // Other notification methods...
}
