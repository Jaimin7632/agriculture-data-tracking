<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Notifications\SensorAlertEmail;
use Illuminate\Support\Facades\Validator;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $sensorName;
    protected $minValue;
    protected $maxValue;
    protected $actualValue;

    /**
     * Create a new job instance.
     *
     * @param string $email
     * @param string $subject
     * @param string $content
     */
    public function __construct($user="", $sensorName="", $minValue="", $maxValue="", $actualValue="")
    {   
        $this->user = $user;
        $this->sensorName = $sensorName;
        $this->minValue = $minValue;
        $this->maxValue = $maxValue;
        $this->actualValue = $actualValue;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {   
        // $email = $this->user['email'];

        $email = $this->user['email'];

        // Validate the email address
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email:rfc'
        ]);

        if ($validator->fails()) {
            // Handle invalid email
            throw new \Exception("Email '{$email}' does not comply with addr-spec of RFC 2822.");
        }
        // echo $email; die();
        Mail::to($email)->send(new SensorAlertEmail($this->user, $this->sensorName, $this->minValue, $this->maxValue, $this->actualValue));
        //$this->user->notify(new NotificationEmail($this->user, $this->sensorName, $this->minValue, $this->maxValue));

    }
}
