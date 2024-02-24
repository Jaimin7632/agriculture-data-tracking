<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Notifications\SensorAlertEmail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $sensorName;
    protected $minValue;
    protected $maxValue;

    /**
     * Create a new job instance.
     *
     * @param string $email
     * @param string $subject
     * @param string $content
     */
    public function __construct($user="", $sensorName="", $minValue="", $maxValue="")
    {   
        $this->user = $user;
        $this->sensorName = $sensorName;
        $this->minValue = $minValue;
        $this->maxValue = $maxValue;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {   
        $email = 'kdpatel1110@gmail.com';
        Mail::to($email)->send(new SensorAlertEmail($this->user, $this->sensorName, $this->minValue, $this->maxValue));
        //$this->user->notify(new NotificationEmail($this->user, $this->sensorName, $this->minValue, $this->maxValue));

    }
}
