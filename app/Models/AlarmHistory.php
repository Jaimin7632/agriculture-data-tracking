<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Jenssegers\Mongodb\Auth\User as Authenticatable;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Carbon\Carbon;

class AlarmHistory extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $collection = 'alarmshistory';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'user_id',
        'device_id',
        'alarmvalue',
        'actualvalue',
        'sensorname',
    ];


}
