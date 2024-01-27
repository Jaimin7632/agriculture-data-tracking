<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Jenssegers\Mongodb\Auth\User as Authenticatable;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Carbon\Carbon;

class Country extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $collection = 'countries';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'code',
        'timezone',
    ];

    public static function changedateBytimezone($createdat = "", $timezone = ""){

        $fromTimezone = 'UTC';
        $carbonDate = Carbon::parse($createdat, $fromTimezone);
        // Change the timezone to the desired timezone
        $convertedDate = $createdat;
        if ($timezone != "") {
            $convertedDate = $carbonDate->setTimezone($timezone);
        }

        return $convertedDate;
    }

}
