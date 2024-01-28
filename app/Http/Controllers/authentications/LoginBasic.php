<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use DateTimeZone;
use DateTime;

class LoginBasic extends Controller
{

	use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

  public function index()
  {
    return view('content.authentications.auth-login-basic');
  }

  static function getIdentifiers()
    {
       static $identifiers = array(
            "Africa/Cairo" => " Cairo",
            "Africa/Casablanca" => " Casablanca",
            "Africa/Harare" => " Harare",
            "Africa/Johannesburg" => " Pretoria",
            "Africa/Lagos" => " West Central Africa",
            "Africa/Monrovia" => " Monrovia",
            "Africa/Nairobi" => " Nairobi",
            "America/Argentina/Buenos_Aires" => " Buenos Aires",
            "America/Argentina/Buenos_Aires" => " Georgetown",
            "America/Bogota" => " Quito",
            "America/Bogota" => " Bogota",
            "America/Caracas" => " Caracas",
            "America/Chihuahua" => " La Paz",
            "America/Chihuahua" => " Chihuahua",
            "America/Godthab" => " Greenland",
            "America/La_Paz" => " La Paz",
            "America/Lima" => " Lima",
            "America/Los_Angeles" => " Pacific Time (US & Canada)",
            "America/Managua" => " Central America",
            "America/Mazatlan" => " Mazatlan",
            "America/Mexico_City" => " Mexico City",
            "America/Mexico_City" => " Guadalajara",
            "America/Monterrey" => " Monterrey",
            "America/Noronha" => " Mid-Atlantic",
            "America/Santiago" => " Santiago",
            "America/Sao_Paulo" => " Brasilia",
            "America/Tijuana" => " Tijuana",
            "Asia/Almaty" => " Almaty",
            "Asia/Baghdad" => " Baghdad",
            "Asia/Baku" => " Baku",
            "Asia/Bangkok" => " Hanoi",
            "Asia/Bangkok" => " Bangkok",
            "Asia/Calcutta" => " Chennai",
            "Asia/Calcutta" => " Mumbai",
            "Asia/Calcutta" => " New Delhi",
            "Asia/Calcutta" => " Sri Jayawardenepura",
            "Asia/Chongqing" => " Chongqing",
            "Asia/Dhaka" => " Dhaka",
            "Asia/Dhaka" => " Astana",
            "Asia/Hong_Kong" => " Beijing",
            "Asia/Hong_Kong" => " Hong Kong",
            "Asia/Irkutsk" => " Irkutsk",
            "Asia/Jakarta" => " Jakarta",
            "Asia/Jerusalem" => " Jerusalem",
            "Asia/Kabul" => " Kabul",
            "Asia/Kamchatka" => " Kamchatka",
            "Asia/Karachi" => " Karachi",
            "Asia/Karachi" => " Islamabad",
            "Asia/Katmandu" => " Kathmandu",
            "Asia/Kolkata" => " Kolkata",
            "Asia/Krasnoyarsk" => " Krasnoyarsk",
            "Asia/Kuala_Lumpur" => " Kuala Lumpur",
            "Asia/Kuwait" => " Kuwait",
            "Asia/Magadan" => " Solomon Is.",
            "Asia/Magadan" => " Magadan",
            "Asia/Magadan" => " New Caledonia",
            "Asia/Muscat" => " Abu Dhabi",
            "Asia/Muscat" => " Muscat",
            "Asia/Novosibirsk" => " Novosibirsk",
            "Asia/Rangoon" => " Rangoon",
            "Asia/Riyadh" => " Riyadh",
            "Asia/Seoul" => " Seoul",
            "Asia/Singapore" => " Singapore",
            "Asia/Taipei" => " Taipei",
            "Asia/Tashkent" => " Tashkent",
            "Asia/Tbilisi" => " Tbilisi",
            "Asia/Tehran" => " Tehran",
            "Asia/Tokyo" => " Osaka",
            "Asia/Tokyo" => " Tokyo",
            "Asia/Tokyo" => " Sapporo",
            "Asia/Ulan_Bator" => " Ulaan Bataar",
            "Asia/Urumqi" => " Urumqi",
            "Asia/Vladivostok" => " Vladivostok",
            "Asia/Yakutsk" => " Yakutsk",
            "Asia/Yekaterinburg" => " Ekaterinburg",
            "Asia/Yerevan" => " Yerevan",
            "Atlantic/Azores" => " Azores",
            "Atlantic/Cape_Verde" => " Cape Verde Is.",
            "Australia/Adelaide" => " Adelaide",
            "Australia/Brisbane" => " Brisbane",
            "Australia/Canberra" => " Canberra",
            "Australia/Darwin" => " Darwin",
            "Australia/Hobart" => " Hobart",
            "Australia/Melbourne" => " Melbourne",
            "Australia/Perth" => " Perth",
            "Australia/Sydney" => " Sydney",
            "Canada/Atlantic" => " Atlantic Time (Canada)",
            "Canada/Newfoundland" => " Newfoundland",
            "Canada/Saskatchewan" => " Saskatchewan",
            "Etc/Greenwich" => " Greenwich Mean Time : Dublin",
            "Europe/Amsterdam" => " Amsterdam",
            "Europe/Athens" => " Athens",
            "Europe/Belgrade" => " Belgrade",
            "Europe/Berlin" => " Berlin",
            "Europe/Berlin" => " Bern",
            "Europe/Bratislava" => " Bratislava",
            "Europe/Brussels" => " Brussels",
            "Europe/Bucharest" => " Bucharest",
            "Europe/Budapest" => " Budapest",
            "Europe/Copenhagen" => " Copenhagen",
            "Europe/Helsinki" => " Helsinki",
            "Europe/Helsinki" => " Kyiv",
            "Europe/Istanbul" => " Istanbul",
            "Europe/Lisbon" => " Lisbon",
            "Europe/Ljubljana" => " Ljubljana",
            "Europe/London" => " Edinburgh",
            "Europe/London" => " London",
            "Europe/Madrid" => " Madrid",
            "Europe/Minsk" => " Minsk",
            "Europe/Moscow" => " St. Petersburg",
            "Europe/Moscow" => " Moscow",
            "Europe/Paris" => " Paris",
            "Europe/Prague" => " Prague",
            "Europe/Riga" => " Riga",
            "Europe/Rome" => " Rome",
            "Europe/Sarajevo" => " Sarajevo",
            "Europe/Skopje" => " Skopje",
            "Europe/Sofia" => " Sofia",
            "Europe/Stockholm" => " Stockholm",
            "Europe/Tallinn" => " Tallinn",
            "Europe/Vienna" => " Vienna",
            "Europe/Vilnius" => " Vilnius",
            "Europe/Volgograd" => " Volgograd",
            "Europe/Warsaw" => " Warsaw",
            "Europe/Zagreb" => " Zagreb",
            "Pacific/Auckland" => " Wellington",
            "Pacific/Auckland" => " Auckland",
            "Pacific/Fiji" => " Fiji",
            "Pacific/Fiji" => " Marshall Is.",
            "Pacific/Guam" => " Guam",
            "Pacific/Honolulu" => " Hawaii",
            "Pacific/Kwajalein" => " International Date Line West",
            "Pacific/Midway" => " Midway Island",
            "Pacific/Port_Moresby" => " Port Moresby",
            "Pacific/Samoa" => " Samoa",
            "Pacific/Tongatapu" => " Nuku'alofa",
            "US/Alaska" => " Alaska",
            "US/Arizona" => " Arizona",
            "US/Central" => " Central Time (US & Canada)",
            "US/East-Indiana" => " Indiana (East)",
            "US/Eastern" => " Eastern Time (US & Canada)",
            "US/Mountain" => " Mountain Time (US & Canada)",
            "UTC" => " UTC"
        );

        return $identifiers;
    }

  protected function authenticated(Request $request, $user) {
           
         echo $user->expiry_date; 
         echo date("Y-m-d");
        if ($user->expiry_date == "") {
            $expiry_date = date("Y-m-d");
        }else{
            $expiry_date = $user->expiry_date;
        }
        if ($user->status == 'inactive' || $expiry_date < date("Y-m-d")) {
            \Auth::logout();
            return redirect(url('/login'))->with('message', 'YOUR SUBSCRIPTION EXPIRED PLEASE CONTACT ADMIN.');
        }

        if ($user->role == "admin") {
            return redirect('/');
        } else {
            return redirect('/');
        }
    }

    public function logout() {
        \Auth::logout();
        return redirect(url('/login'));
    }

    public function webpage(Request $request){
        echo "hello"; exit();
    }

}
