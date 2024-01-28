<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_timezones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('timezone');
            $table->string('utc_offset');
            $table->timestamps();
        });

        // Insert time zone

        $seeds = array(
                array(
                'timezone' => 'Pacific/Midway',
                'utc_offset' => 'UTC-11:00',
                'name' => 'Midway Island'
                ),
                array(
                'timezone' => 'Pacific/Honolulu',
                'utc_offset' => 'UTC-10:00',
                'name' => 'Hawaii'
                ),
                array(
                'timezone' => 'America/Anchorage',
                'utc_offset' => 'UTC-08:00',
                'name' => 'Alaska'
                ),
                array(
                'timezone' => 'America/Tijuana',
                'utc_offset' => 'UTC-07:00',
                'name' => 'Baja California'
                ),
                array(
                'timezone' => 'America/Los_Angeles',
                'utc_offset' => 'UTC-07:00',
                'name' => 'Pacific Time US and Canada)'
                ),
                array(
                'timezone' => 'America/Phoenix',
                'utc_offset' => 'UTC-07:00',
                'name' => 'Arizona'
                ),
                array(
                'timezone' => 'America/Chihuahua',
                'utc_offset' => 'UTC-06:00',
                'name' => 'Chihuahua'
                ),
                array(
                'timezone' => 'America/Denver',
                'utc_offset' => 'UTC-06:00',
                'name' => 'Mountain Time US and Canada)'
                ),
                array(
                'timezone' => 'America/Belize',
                'utc_offset' => 'UTC-06:00',
                'name' => 'Central America'
                ),
                array(
                'timezone' => 'America/Chicago',
                'utc_offset' => 'UTC-05:00',
                'name' => 'Central Time US and Canada)'
                ),
                array(
                'timezone' => 'America/Mexico_City',
                'utc_offset' => 'UTC-05:00',
                'name' => 'Guadalajara'
                ),
                array(
                'timezone' => 'America/Regina',
                'utc_offset' => 'UTC-06:00',
                'name' => 'Saskatchewan'
                ),
                array(
                'timezone' => 'America/Bogota',
                'utc_offset' => 'UTC-05:00',
                'name' => 'Bogota'
                ),
                array(
                'timezone' => 'America/Jamaica',
                'utc_offset' => 'UTC-05:00',
                'name' => 'Kingston'
                ),
                array(
                'timezone' => 'America/New_York',
                'utc_offset' => 'UTC-04:00',
                'name' => 'Eastern Time US and Canada)'
                ),
                array(
                'timezone' => 'America/Indiana/Indianapolis',
                'utc_offset' => 'UTC-04:00',
                'name' => 'Indiana East)'
                ),
                array(
                'timezone' => 'America/Caracas',
                'utc_offset' => 'UTC-04:30',
                'name' => 'Caracas'
                ),
                array(
                'timezone' => 'America/Asuncion',
                'utc_offset' => 'UTC-03:00',
                'name' => 'Asuncion'
                ),
                array(
                'timezone' => 'America/Halifax',
                'utc_offset' => 'UTC-03:00',
                'name' => 'Atlantic Time Canada)'
                ),
                array(
                'timezone' => 'America/Cuiaba',
                'utc_offset' => 'UTC-04:00',
                'name' => 'Cuiaba'
                ),
                array(
                'timezone' => 'America/Manaus',
                'utc_offset' => 'UTC-04:00',
                'name' => 'Georgetown'
                ),
                array(
                'timezone' => 'America/St_Johns',
                'utc_offset' => 'UTC-02:30',
                'name' => 'Newfoundland and Labrador'
                ),
                array(
                'timezone' => 'America/Sao_Paulo',
                'utc_offset' => 'UTC-03:00',
                'name' => 'Brasilia'
                ),
                array(
                'timezone' => 'America/Buenos_Aires',
                'utc_offset' => 'UTC-03:00',
                'name' => 'Buenos Aires'
                ),
                array(
                'timezone' => 'America/Cayenne',
                'utc_offset' => 'UTC-03:00',
                'name' => 'Cayenne'
                ),
                array(
                'timezone' => 'America/Godthab',
                'utc_offset' => 'UTC-02:00',
                'name' => 'Greenland'
                ),
                array(
                'timezone' => 'America/Montevideo',
                'utc_offset' => 'UTC-03:00',
                'name' => 'Montevideo'
                ),
                array(
                'timezone' => 'America/Bahia',
                'utc_offset' => 'UTC-03:00',
                'name' => 'Salvador'
                ),
                array(
                'timezone' => 'America/Santiago',
                'utc_offset' => 'UTC-03:00',
                'name' => 'Santiago'
                ),
                array(
                'timezone' => 'America/Noronha',
                'utc_offset' => 'UTC-02:00',
                'name' => 'Mid-Atlantic'
                ),
                array(
                'timezone' => 'Atlantic/Azores',
                'utc_offset' => 'UTC+00:00',
                'name' => 'Azores'
                ),
                array(
                'timezone' => 'Atlantic/Cape_Verde',
                'utc_offset' => 'UTC-01:00',
                'name' => 'Cape Verde Islands'
                ),
                array(
                'timezone' => 'Europe/London',
                'utc_offset' => 'UTC+01:00',
                'name' => 'Dublin'
                ),
                array(
                'timezone' => 'Africa/Casablanca',
                'utc_offset' => 'UTC+01:00',
                'name' => 'Casablanca'
                ),
                array(
                'timezone' => 'Africa/Monrovia',
                'utc_offset' => 'UTC+00:00',
                'name' => 'Monrovia'
                ),
                array(
                'timezone' => 'Europe/Amsterdam',
                'utc_offset' => 'UTC+02:00',
                'name' => 'Amsterdam'
                ),
                array(
                'timezone' => 'Europe/Belgrade',
                'utc_offset' => 'UTC+02:00',
                'name' => 'Belgrade'
                ),
                array(
                'timezone' => 'Europe/Brussels',
                'utc_offset' => 'UTC+02:00',
                'name' => 'Brussels'
                ),
                array(
                'timezone' => 'Europe/Warsaw',
                'utc_offset' => 'UTC+02:00',
                'name' => 'Sarajevo'
                ),
                array(
                'timezone' => 'Africa/Algiers',
                'utc_offset' => 'UTC+01:00',
                'name' => 'West Central Africa'
                ),
                array(
                'timezone' => 'Africa/Windhoek',
                'utc_offset' => 'UTC+02:00',
                'name' => 'Windhoek'
                ),
                array(
                'timezone' => 'Europe/Athens',
                'utc_offset' => 'UTC+03:00',
                'name' => 'Athens'
                ),
                array(
                'timezone' => 'Asia/Beirut',
                'utc_offset' => 'UTC+03:00',
                'name' => 'Beirut'
                ),
                array(
                'timezone' => 'Africa/Cairo',
                'utc_offset' => 'UTC+02:00',
                'name' => 'Cairo'
                ),
                array(
                'timezone' => 'Asia/Damascus',
                'utc_offset' => 'UTC+03:00',
                'name' => 'Damascus'
                ),
                array(
                'timezone' => 'EET',
                'utc_offset' => 'UTC+03:00',
                'name' => 'Eastern Europe'
                ),
                array(
                'timezone' => 'Africa/Harare',
                'utc_offset' => 'UTC+02:00',
                'name' => 'Harare'
                ),
                array(
                'timezone' => 'Europe/Helsinki',
                'utc_offset' => 'UTC+03:00',
                'name' => 'Helsinki'
                ),
                array(
                'timezone' => 'Asia/Istanbul',
                'utc_offset' => 'UTC+03:00',
                'name' => 'Istanbul'
                ),
                array(
                'timezone' => 'Asia/Jerusalem',
                'utc_offset' => 'UTC+03:00',
                'name' => 'Jerusalem'
                ),
                array(
                'timezone' => 'Europe/Kaliningrad',
                'utc_offset' => 'UTC+02:00',
                'name' => 'Kaliningrad'
                ),
                array(
                'timezone' => 'Africa/Tripoli',
                'utc_offset' => 'UTC+02:00',
                'name' => 'Tripoli'
                ),
                array(
                'timezone' => 'Asia/Amman',
                'utc_offset' => 'UTC+03:00',
                'name' => 'Amman'
                ),
                array(
                'timezone' => 'Asia/Baghdad',
                'utc_offset' => 'UTC+03:00',
                'name' => 'Baghdad'
                ),
                array(
                'timezone' => 'Asia/Kuwait',
                'utc_offset' => 'UTC+03:00',
                'name' => 'Kuwait'
                ),
                array(
                'timezone' => 'Europe/Minsk',
                'utc_offset' => 'UTC+03:00',
                'name' => 'Minsk'
                ),
                array(
                'timezone' => 'Europe/Moscow',
                'utc_offset' => 'UTC+03:00',
                'name' => 'Moscow'
                ),
                array(
                'timezone' => 'Africa/Nairobi',
                'utc_offset' => 'UTC+03:00',
                'name' => 'Nairobi'
                ),
                array(
                'timezone' => 'Asia/Tehran',
                'utc_offset' => 'UTC+03:30',
                'name' => 'Tehran'
                ),
                array(
                'timezone' => 'Asia/Muscat',
                'utc_offset' => 'UTC+04:00',
                'name' => 'Abu Dhabi'
                ),
                array(
                'timezone' => 'Asia/Baku',
                'utc_offset' => 'UTC+05:00',
                'name' => 'Baku'
                ),
                array(
                'timezone' => 'Europe/Samara',
                'utc_offset' => 'UTC+04:00',
                'name' => 'Izhevsk'
                ),
                array(
                'timezone' => 'Indian/Mauritius',
                'utc_offset' => 'UTC+04:00',
                'name' => 'Port Louis'
                ),
                array(
                'timezone' => 'Asia/Tbilisi',
                'utc_offset' => 'UTC+04:00',
                'name' => 'Tbilisi'
                ),
                array(
                'timezone' => 'Asia/Yerevan',
                'utc_offset' => 'UTC+04:00',
                'name' => 'Yerevan'
                ),
                array(
                'timezone' => 'Asia/Kabul',
                'utc_offset' => 'UTC+04:30',
                'name' => 'Kabul'
                ),
                array(
                'timezone' => 'Asia/Tashkent',
                'utc_offset' => 'UTC+05:00',
                'name' => 'Tashkent'
                ),
                array(
                'timezone' => 'Asia/Yekaterinburg',
                'utc_offset' => 'UTC+05:00',
                'name' => 'Ekaterinburg'
                ),
                array(
                'timezone' => 'Asia/Karachi',
                'utc_offset' => 'UTC+05:00',
                'name' => 'Islamabad'
                ),
                array(
                'timezone' => 'Asia/Kolkata',
                'utc_offset' => 'UTC+05:30',
                'name' => 'Chennai'
                ),
                array(
                'timezone' => 'Asia/Colombo',
                'utc_offset' => 'UTC+05:30',
                'name' => 'Sri Jayawardenepura'
                ),
                array(
                'timezone' => 'Asia/Katmandu',
                'utc_offset' => 'UTC+05:45',
                'name' => 'Kathmandu'
                ),
                array(
                'timezone' => 'Asia/Almaty',
                'utc_offset' => 'UTC+06:00',
                'name' => 'Astana'
                ),
                array(
                'timezone' => 'Asia/Dhaka',
                'utc_offset' => 'UTC+06:00',
                'name' => 'Dhaka'
                ),
                array(
                'timezone' => 'Asia/Novosibirsk',
                'utc_offset' => 'UTC+06:00',
                'name' => 'Novosibirsk'
                ),
                array(
                'timezone' => 'Asia/Rangoon',
                'utc_offset' => 'UTC+06:30',
                'name' => 'Yangon Rangoon)'
                ),
                array(
                'timezone' => 'Asia/Bangkok',
                'utc_offset' => 'UTC+07:00',
                'name' => 'Bangkok'
                ),
                array(
                'timezone' => 'Asia/Krasnoyarsk',
                'utc_offset' => 'UTC+07:00',
                'name' => 'Krasnoyarsk'
                ),
                array(
                'timezone' => 'Asia/Chongqing',
                'utc_offset' => 'UTC+08:00',
                'name' => 'Beijing'
                ),
                array(
                'timezone' => 'Asia/Irkutsk',
                'utc_offset' => 'UTC+08:00',
                'name' => 'Irkutsk'
                ),
                array(
                'timezone' => 'Asia/Kuala_Lumpur',
                'utc_offset' => 'UTC+08:00',
                'name' => 'Kuala Lumpur'
                ),
                array(
                'timezone' => 'Australia/Perth',
                'utc_offset' => 'UTC+08:00',
                'name' => 'Perth'
                ),
                array(
                'timezone' => 'Asia/Taipei',
                'utc_offset' => 'UTC+08:00',
                'name' => 'Taipei'
                ),
                array(
                'timezone' => 'Asia/Ulaanbaatar',
                'utc_offset' => 'UTC+08:00',
                'name' => 'Ulaanbaatar'
                ),
                array(
                'timezone' => 'Asia/Tokyo',
                'utc_offset' => 'UTC+09:00',
                'name' => 'Osaka'
                ),
                array(
                'timezone' => 'Asia/Seoul',
                'utc_offset' => 'UTC+09:00',
                'name' => 'Seoul'
                ),
                array(
                'timezone' => 'Asia/Yakutsk',
                'utc_offset' => 'UTC+09:00',
                'name' => 'Yakutsk'
                ),
                array(
                'timezone' => 'Australia/Adelaide',
                'utc_offset' => 'UTC+10:30',
                'name' => 'Adelaide'
                ),
                array(
                'timezone' => 'Australia/Darwin',
                'utc_offset' => 'UTC+09:30',
                'name' => 'Darwin'
                ),
                array(
                'timezone' => 'Australia/Brisbane',
                'utc_offset' => 'UTC+10:00',
                'name' => 'Brisbane'
                ),
                array(
                'timezone' => 'Australia/Canberra',
                'utc_offset' => 'UTC+11:00',
                'name' => 'Canberra'
                ),
                array(
                'timezone' => 'Pacific/Guam',
                'utc_offset' => 'UTC+10:00',
                'name' => 'Guam'
                ),
                array(
                'timezone' => 'Australia/Hobart',
                'utc_offset' => 'UTC+11:00',
                'name' => 'Hobart'
                ),
                array(
                'timezone' => 'Asia/Magadan',
                'utc_offset' => 'UTC+10:00',
                'name' => 'Magadan'
                ),
                array(
                'timezone' => 'Asia/Vladivostok',
                'utc_offset' => 'UTC+10:00',
                'name' => 'Vladivostok'
                ),
                array(
                'timezone' => 'Asia/Srednekolymsk',
                'utc_offset' => 'UTC+11:00',
                'name' => 'Chokirdakh'
                ),
                array(
                'timezone' => 'Pacific/Guadalcanal',
                'utc_offset' => 'UTC+11:00',
                'name' => 'Solomon Islands'
                ),
                array(
                'timezone' => 'Asia/Anadyr',
                'utc_offset' => 'UTC+12:00',
                'name' => 'Anadyr'
                ),
                array(
                'timezone' => 'Pacific/Auckland',
                'utc_offset' => 'UTC+13:00',
                'name' => 'Auckland'
                ),
                array(
                'timezone' => 'Pacific/Fiji',
                'utc_offset' => 'UTC+12:00',
                'name' => 'Fiji Islands'
                ),
                array(
                'timezone' => 'Pacific/Tongatapu',
                'utc_offset' => 'UTC+13:00',
                'name' => 'Nuku alofa'
                ),
                array(
                'timezone' => 'Pacific/Apia',
                'utc_offset' => 'UTC+14:00',
                'name' => 'Samoa'
                )
            );

        foreach ($seeds as $seed) {
            // DB::table('mst_timezones')create($seed);
            DB::table('mst_timezones')->insert($seed);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_timezones');
    }
};
