<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jenssegers\Mongodb\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use App\Models\ChangeDeviceName;
use App\Models\ChangeGraphName;
use App\Models\User;
use App\Models\SensorData;
use App\Models\SetLatLong;
use App\Models\Setalarm;
use App\Models\AlarmHistory;
use App\Models\Country;
use App\Models\Attributes;
use Carbon\Carbon;
use DB;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Analytics extends Controller
{

	public function __construct()
    {
        $this->middleware('auth');
    }

  public function index()
  {
    // return view('content.dashboard.dashboards-analytics');
    $user = Auth::user();

    if ($user->role == 'admin') {
       $currentDate = Carbon::now();
       $totalusercount = User::count();
       $activeusercount = User::where('status', 'active')->count();
       $inactiveusercount = User::where('status', 'inactive')->count();
       $adminusercount = User::where('role', 'admin')->count();
       $inactiveusers= User::where('status', 'inactive')->get()->toArray();
       // echo "<pre>"; print_r($inactiveusers); die();
       $sensordatacount = SensorData::count();
        $futureDate1 = $currentDate->subDays(2);
        $twoDaysAgo = $futureDate1->format('Y-m-d');
       $uniqueDeviceCount = SensorData::where('created_at', '>=', $twoDaysAgo)->groupBy('device_id')->distinct()->count('device_id');
        // Calculate the date 30 days from now

        $futureDate = $currentDate->addDays(30);
        $formattedFutureDate = $futureDate->format('Y-m-d');
        $expiryusers = User::whereBetween('expiry_date', [date('Y-m-d'), $formattedFutureDate])->get()->toArray();

       return view('content/dashboard/dashboards-analytics', compact('user','totalusercount','activeusercount','inactiveusercount','adminusercount','sensordatacount','uniqueDeviceCount','inactiveusers','expiryusers'));
    }else{
       return view('content/dashboard/userdashboards-analytics', compact('user'));
    }

  }

  public function getgraphdata(Request $request){

    $authuser = Auth::user();

    $message = "Failure";
    $sensor_data = [];
    $soialSensorValues = [];
    $pressureSensorValues = [];
    $humiditySensorValues = [];
    $temperatureSensorValues = [];
    $post_data = $request->all();
    $User_Id = $post_data['User_Id'];
    $device_id = $post_data['device_id'];
    $fromDate = $post_data['from_date'];
    $toDate = $post_data['to_date'];

    $changetime = $post_data['changetime'];
    $changematrix = $post_data['changematrix'];

    // echo "<pre>"; print_r($post_data); die();

    if (!empty($device_id)) {
       // echo $post_data['device_id']; die();
      if ($fromDate != '' && $toDate != '') {
          $fromDate .= ' 00:00:00'; // Concatenate time for start of the day
          $toDate .= ' 23:59:59'; // Concatenate time for end of the day
          $sensor_data = SensorData::where('device_id', $device_id)
              ->whereBetween('created_at', [$fromDate, $toDate])
              ->orderByDesc('created_at')
              ->limit(15)
              ->get()
              ->toArray();

      } 
      else if($changetime != '' && $changematrix != ''){

        $latestRecord = SensorData::where('device_id', $device_id)
                    ->orderByDesc('created_at')
                    ->first();

        if (!$latestRecord) {
            // If there are no records, return an appropriate response
            return null;
        }

        // Calculate the start time based on the last inserted record's created_at time
        // Parse the latest record's created_at timestamp
        $latestCreatedAt = Carbon::parse($latestRecord->created_at);

        // Calculate the start time based on the last inserted record's created_at time
        $startTime = $latestCreatedAt->subMinutes($changetime);
        $startTimeString = $startTime->toDateTimeString();
        // echo "<pre>"; print_r($startTimeString); die();       
        // Build the base query
        $query = SensorData::where('device_id', $device_id)
                    ->where('created_at', '>=', $startTimeString)
                    ->orderByDesc('created_at')
                    ->limit(15);

        // Apply the statistic function
        switch ($changematrix) {
            case 'max':
                $sensor_data = $query->max('value'); // Replace 'value' with the actual column name
                break;
            case 'min':
                $sensor_data = $query->min('value'); // Replace 'value' with the actual column name
                break;
            case 'avg':
                $sensor_data = $query->avg('value'); // Replace 'value' with the actual column name
                break;
            default:
                $sensor_data = $query->get()->toArray();
                break;
        }    
      //   $aggregationFunction = 'AVG';

      //   // Check the value of $changematrix and set the aggregation function accordingly
      //   switch ($changematrix) {
      //       case 'MIN':
      //           $aggregationFunction = 'MIN';
      //           break;
      //       case 'MAX':
      //           $aggregationFunction = 'MAX';
      //           break;
      //       // If $changematrix is not MIN or MAX, it defaults to AVG
      //   }
      //     $sensor_data_query = SensorData::select(
      //         DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d %H:") AS time_hour'),
      //         DB::raw('FLOOR(MINUTE(created_at) / ' . $changetime . ') AS created_at'),
      //         DB::raw($aggregationFunction . '(sensor_value) AS sensor_value')
      //     )
      //     ->where('device_id', $device_id)
      //     ->groupBy('time_hour', 'created_at')
      //     ->orderBy('time_hour', 'asc')
      //     ->orderBy('time_minute', 'asc');

      // // Execute the query
      // $sensor_data = $sensor_data_query->get()->toArray();
      // echo "<pre>"; print_r($result); die();
      }

      else {
          $sensor_data = SensorData::where('device_id', $device_id)->orderByDesc('created_at')->limit(15)
              ->get()
              ->toArray();
      }
      // echo "<pre>"; print_r($sensor_data); die();
      $outputArray = [];
      $sensorConfig = config('global');
      $sensorValues = [];
      $sensorColors = [];
      foreach (array_reverse($sensor_data) as $item) {
          $formattedDateTime = $item['created_at'];
          $dateTime = new \DateTime($formattedDateTime);
          $createdAt = $dateTime->format('Y-m-d H:i:s');
          
          $changedateBycountry =  Country::changedateBytimezone($dateTime, $authuser->timezone)->format('Y-m-d H:i:s');
          $temperatureValues = [];
          $humidityValues = [];
          // Initialize an array to store sensor values dynamically
          ksort($item['sensor_data']);
          foreach ($item['sensor_data'] as $sensorName => $sensorDetails) {
             
              // $graphname = ChangeGraphName::where('original_name', 'like', '%' . $sensorName . '%')->where('device_id', $device_id)->where('user_id', $User_Id)->first();
              // $sensorValues[$sensorName]['changename'] = $sensorName;
              // if (!empty($graphname)) {
              //   $sensorValues[$sensorName]['changename'] = $graphname->change_name;
              // }

              $sensorValueKey = $sensorName;
              $sensorValueType = isset($sensor_data[0]['sensor_data'][$sensorValueKey]['type']) ? $sensor_data[0]['sensor_data'][$sensorValueKey]['type'] : "graph" ;
              $sensorValueColor = $sensorDetails['unit'];
                // Add sensor values to the dynamically generated array
              $sensorValues[$sensorName]['type'] = $sensorValueType;
              $sensorValues[$sensorName]['data'][] = ['x' => $changedateBycountry, 'y' => round($sensorDetails['value'],2)];
                
              $sensorValues[$sensorName]['spname'] = $sensorName;
              $sensorValues[$sensorName]['unit'] = $sensorDetails['unit'];
              
              if (strpos($sensorName, "pressure") !== false) {
                $sensorValues[$sensorName]['color'] = '#33FF57';
                $sensorValues[$sensorName]['icon'] = '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="#33FF57" width="50"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M14,19V17.73a8,8,0,1,0-4,0V19H2v3H22V19ZM6.12,11.17A5.9,5.9,0,0,1,6.46,7.7a6,6,0,1,1,7.84,7.84,5.9,5.9,0,0,1-3.47.34,6,6,0,0,1-4.71-4.71ZM11,14.05A1.41,1.41,0,0,1,10.5,13c.17-1.9,1.5-7.5,1.5-7.5s1.33,5.6,1.5,7.5a1.39,1.39,0,0,1-.45,1.05h0l0,0A1.45,1.45,0,0,1,11,14.05ZM7,10H8v1H7Zm9,0h1v1H16Zm0-2H15V7h1ZM8,7H9V8H8Zm3-1H10V5h1Zm3,0H13V5h1Z"></path><rect width="24" height="24" fill="none"></rect></g></svg>';
              } else if (strpos($sensorName, "SoilWetness") !== false){
                $sensorValues[$sensorName]['color'] = '#FF5733';
                $sensorValues[$sensorName]['icon'] = '<svg viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg" fill="#FF5733" width="50">
                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                <g id="SVGRepo_iconCarrier">
                  <defs>
                    <style>.cls-1{fill:#FF5733;}</style>
                  </defs>
                  <g id="Soil">
                    <path class="cls-1" d="M22,75.18H20a1,1,0,0,0,0,2h2a1,1,0,0,0,0-2Z"></path>
                    <path class="cls-1" d="M30,72.26H28a1,1,0,0,0,0,2h2a1,1,0,0,0,0-2Z"></path>
                    <path class="cls-1" d="M34,78.1H32a1,1,0,0,0,0,2h2a1,1,0,0,0,0-2Z"></path>
                    <path class="cls-1" d="M40,75.18H38a1,1,0,0,0,0,2h2a1,1,0,0,0,0-2Z"></path>
                    <path class="cls-1" d="M48,77.13H46a1,1,0,0,0,0,2h2a1,1,0,0,0,0-2Z"></path>
                    <path class="cls-1" d="M53,73.23H51a1,1,0,0,0,0,2h2a1,1,0,0,0,0-2Z"></path>
                    <path class="cls-1" d="M60,75.18H58a1,1,0,0,0,0,2h2a1,1,0,0,0,0-2Z"></path>
                    <path class="cls-1" d="M68,72.26H66a1,1,0,0,0,0,2h2a1,1,0,0,0,0-2Z"></path>
                    <path class="cls-1" d="M72,78.1H70a1,1,0,0,0,0,2h2a1,1,0,0,0,0-2Z"></path>
                    <path class="cls-1" d="M78,75.18H76a1,1,0,0,0,0,2h2a1,1,0,0,0,0-2Z"></path>
                    <path class="cls-1" d="M86,77.13H84a1,1,0,0,0,0,2h2a1,1,0,0,0,0-2Z"></path>
                    <path class="cls-1" d="M91,73.23H89a1,1,0,0,0,0,2h2a1,1,0,0,0,0-2Z"></path>
                    <path class="cls-1" d="M97,75.18H95a1,1,0,0,0,0,2h2a1,1,0,0,0,0-2Z"></path>
                    <path class="cls-1" d="M105,72.26h-2a1,1,0,0,0,0,2h2a1,1,0,0,0,0-2Z"></path>
                    <path class="cls-1" d="M23,62.54a1,1,0,0,0-1-1H20a1,1,0,1,0,0,2h2A1,1,0,0,0,23,62.54Z"></path>
                    <path class="cls-1" d="M28,60.62h2a1,1,0,1,0,0-2H28a1,1,0,0,0,0,2Z"></path>
                    <path class="cls-1" d="M32,66.46h2a1,1,0,0,0,0-2H32a1,1,0,0,0,0,2Z"></path>
                    <path class="cls-1" d="M38,63.54h2a1,1,0,0,0,0-2H38a1,1,0,0,0,0,2Z"></path>
                    <path class="cls-1" d="M46,65.49h2a1,1,0,0,0,0-2H46a1,1,0,0,0,0,2Z"></path>
                    <path class="cls-1" d="M51,61.59h2a1,1,0,0,0,0-2H51a1,1,0,0,0,0,2Z"></path>
                    <path class="cls-1" d="M58,63.54h2a1,1,0,0,0,0-2H58a1,1,0,0,0,0,2Z"></path>
                    <path class="cls-1" d="M66,60.62h2a1,1,0,0,0,0-2H66a1,1,0,0,0,0,2Z"></path>
                    <path class="cls-1" d="M70,66.46h2a1,1,0,0,0,0-2H70a1,1,0,0,0,0,2Z"></path>
                    <path class="cls-1" d="M76,63.54h2a1,1,0,0,0,0-2H76a1,1,0,0,0,0,2Z"></path>
                    <path class="cls-1" d="M84,65.49h2a1,1,0,1,0,0-2H84a1,1,0,0,0,0,2Z"></path>
                    <path class="cls-1" d="M89,61.59h2a1,1,0,0,0,0-2H89a1,1,0,0,0,0,2Z"></path>
                    <path class="cls-1" d="M95,63.54h2a1,1,0,0,0,0-2H95a1,1,0,0,0,0,2Z"></path>
                    <path class="cls-1" d="M103,60.62h2a1,1,0,0,0,0-2h-2a1,1,0,0,0,0,2Z"></path>
                    <path class="cls-1" d="M112,44H16a1,1,0,0,0-1,1V83a1,1,0,0,0,1,1h96a1,1,0,0,0,1-1V45A1,1,0,0,0,112,44ZM17,56.81l6.07-2.94a13,13,0,0,1,9.86,0l1.27.61a13.4,13.4,0,0,0,11.6,0l1.27-.61a13,13,0,0,1,9.86,0l1.27.61a13.4,13.4,0,0,0,11.6,0l1.27-.61a13,13,0,0,1,9.86,0l1.27.61a13.4,13.4,0,0,0,11.6,0l1.27-.61a13,13,0,0,1,9.86,0L111,56.81V70.08l-5.2-2.52a14.87,14.87,0,0,0-11.6,0l-1.27.62a11.43,11.43,0,0,1-9.86,0l-1.27-.62a14.87,14.87,0,0,0-11.6,0l-1.27.62a11.43,11.43,0,0,1-9.86,0l-1.27-.62a14.87,14.87,0,0,0-11.6,0l-1.27.62a11.43,11.43,0,0,1-9.86,0l-1.27-.62a14.87,14.87,0,0,0-11.6,0L17,70.08ZM111,46v8.59l-5.2-2.52a14.87,14.87,0,0,0-11.6,0l-1.27.61a11.36,11.36,0,0,1-9.86,0l-1.27-.61a14.87,14.87,0,0,0-11.6,0l-1.27.61a11.36,11.36,0,0,1-9.86,0l-1.27-.61a14.87,14.87,0,0,0-11.6,0l-1.27.61a11.36,11.36,0,0,1-9.86,0l-1.27-.61a14.87,14.87,0,0,0-11.6,0L17,54.59V46ZM17,82V72.3l6.07-2.94a13,13,0,0,1,9.86,0L34.2,70a13.4,13.4,0,0,0,11.6,0l1.27-.62a13,13,0,0,1,9.86,0L58.2,70a13.4,13.4,0,0,0,11.6,0l1.27-.62a13,13,0,0,1,9.86,0L82.2,70a13.4,13.4,0,0,0,11.6,0l1.27-.62a13,13,0,0,1,9.86,0L111,72.3V82Z"></path>
                  </g>
                </g>
              </svg>';
              } else if (strpos($sensorName, "Humidity") !== false){
                $sensorValues[$sensorName]['color'] = '#5733FF';
                $sensorValues[$sensorName]['icon'] = '<svg viewBox="0 0 24 24" width="50" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M15.0066 3.25608C16.8483 2.85737 19.1331 2.8773 22.2423 3.65268C22.7781 3.78629 23.1038 4.32791 22.9699 4.86241C22.836 5.39691 22.2931 5.7219 21.7573 5.58829C18.8666 4.86742 16.9015 4.88747 15.4308 5.20587C13.9555 5.52524 12.895 6.15867 11.7715 6.84363L11.6874 6.89494C10.6044 7.55565 9.40515 8.28729 7.82073 8.55069C6.17734 8.82388 4.23602 8.58235 1.62883 7.54187C1.11607 7.33724 0.866674 6.75667 1.0718 6.24513C1.27692 5.73359 1.85889 5.48479 2.37165 5.68943C4.76435 6.6443 6.32295 6.77699 7.492 6.58265C8.67888 6.38535 9.58373 5.83916 10.7286 5.14119C11.855 4.45445 13.1694 3.6538 15.0066 3.25608Z" fill="#5733FF"></path> <path d="M22.2423 7.64302C19.1331 6.86765 16.8483 6.84772 15.0066 7.24642C13.1694 7.64415 11.855 8.44479 10.7286 9.13153C9.58373 9.8295 8.67888 10.3757 7.492 10.573C6.32295 10.7673 4.76435 10.6346 2.37165 9.67977C1.85889 9.47514 1.27692 9.72393 1.0718 10.2355C0.866674 10.747 1.11607 11.3276 1.62883 11.5322C4.23602 12.5727 6.17734 12.8142 7.82073 12.541C9.40515 12.2776 10.6044 11.546 11.6874 10.8853L11.7715 10.834C12.895 10.149 13.9555 9.51558 15.4308 9.19621C16.9015 8.87781 18.8666 8.85777 21.7573 9.57863C22.2931 9.71224 22.836 9.38726 22.9699 8.85275C23.1038 8.31825 22.7781 7.77663 22.2423 7.64302Z" fill="#5733FF"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M18.9998 10.0266C18.6526 10.0266 18.3633 10.2059 18.1614 10.4772C18.0905 10.573 17.9266 10.7972 17.7089 11.111C17.4193 11.5283 17.0317 12.1082 16.6424 12.7555C16.255 13.3996 15.8553 14.128 15.5495 14.8397C15.2567 15.5213 14.9989 16.2614 14.9999 17.0117C15.0006 17.2223 15.0258 17.4339 15.0604 17.6412C15.1182 17.9872 15.2356 18.4636 15.4804 18.9521C15.7272 19.4446 16.1131 19.9674 16.7107 20.3648C17.3146 20.7664 18.0748 21 18.9998 21C19.9248 21 20.685 20.7664 21.2888 20.3648C21.8864 19.9674 22.2724 19.4446 22.5192 18.9522C22.764 18.4636 22.8815 17.9872 22.9393 17.6413C22.974 17.4337 22.9995 17.2215 22.9998 17.0107C23.0001 16.2604 22.743 15.5214 22.4501 14.8397C22.1444 14.128 21.7447 13.3996 21.3573 12.7555C20.968 12.1082 20.5803 11.5283 20.2907 11.111C20.073 10.7972 19.909 10.573 19.8382 10.4772C19.6363 10.2059 19.3469 10.0266 18.9998 10.0266ZM20.6119 15.6257C20.3552 15.0281 20.0049 14.3848 19.6423 13.782C19.4218 13.4154 19.2007 13.0702 18.9998 12.7674C18.7989 13.0702 18.5778 13.4154 18.3573 13.782C17.9948 14.3848 17.6445 15.0281 17.3878 15.6257L17.3732 15.6595C17.1965 16.0704 16.9877 16.5562 17.0001 17.0101C17.0121 17.3691 17.1088 17.7397 17.2693 18.0599C17.3974 18.3157 17.574 18.5411 17.8201 18.7048C18.06 18.8643 18.4248 19.0048 18.9998 19.0048C19.5748 19.0048 19.9396 18.8643 20.1795 18.7048C20.4256 18.5411 20.6022 18.3156 20.7304 18.0599C20.8909 17.7397 20.9876 17.3691 20.9996 17.01C21.0121 16.5563 20.8032 16.0705 20.6265 15.6597L20.6119 15.6257Z" fill="#5733FF"></path> <path d="M14.1296 11.5308C14.8899 11.2847 15.4728 12.076 15.1153 12.7892C14.952 13.1151 14.7683 13.3924 14.4031 13.5214C13.426 13.8666 12.6166 14.3527 11.7715 14.8679L11.6874 14.9192C10.6044 15.5799 9.40516 16.3115 7.82074 16.5749C6.17735 16.8481 4.23604 16.6066 1.62884 15.5661C1.11608 15.3615 0.866688 14.7809 1.07181 14.2694C1.27694 13.7578 1.8589 13.509 2.37167 13.7137C4.76436 14.6685 6.32297 14.8012 7.49201 14.6069C8.67889 14.4096 9.58374 13.8634 10.7286 13.1654C11.8166 12.5021 12.9363 11.9171 14.1296 11.5308Z" fill="#5733FF"></path> </g></svg>';
              } else if (strpos($sensorName, "AirTemperature") !== false){ 
                $sensorValues[$sensorName]['color'] = '#FF33C7';
                $sensorValues[$sensorName]['icon'] = '<svg viewBox="0 0 24 24" width="50" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M15 4H20M15 8H20M17 12H20M8 15.9998C7.44772 15.9998 7 16.4475 7 16.9998C7 17.5521 7.44772 17.9998 8 17.9998C8.55228 17.9998 9 17.5521 9 16.9998C9 16.4475 8.55228 15.9998 8 15.9998ZM8 15.9998V9M8 16.9998L8.00707 17.0069M12 16.9998C12 19.209 10.2091 20.9998 8 20.9998C5.79086 20.9998 4 19.209 4 16.9998C4 15.9854 4.37764 15.0591 5 14.354L5 6C5 4.34315 6.34315 3 8 3C9.65685 3 11 4.34315 11 6V14.354C11.6224 15.0591 12 15.9854 12 16.9998Z" stroke="#FF33C7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>';
              }
              
              // if (strpos($sensorName, "AirTemperature") !== false) {
              //     $temperatureValues[] = ['value' => $sensorDetails['value'], 'type' => $sensorValueType];
              // }
              
              // if (strpos($sensorName, "Humidity") !== false) {
              //     $humidityValues[] = ['value' => $sensorDetails['value'], 'type' => $sensorValueType];
              // }
              
          }
          // echo "<pre>"; print_r($temperatureValues); die();
          // foreach ($temperatureValues as $key => $temperatureCelsius) {
          //     // Check if the corresponding humidity value exists
          //     if (isset($humidityValues[$key])) {

          //       $temperatureCelsiusval = $temperatureCelsius['value'];
          //       $temperatureType = $temperatureCelsius['type'];

          //       // Get the humidity value and its type
          //       $humidityValue = $humidityValues[$key]['value'];
          //       $humidityType = $humidityValues[$key]['type'];

          //       if ($temperatureCelsiusval != '' && $humidityValue != '') {
          //         // Calculate the DPV for the current set of temperature and humidity values
          //         $dewPoint = $this->calculateDewPoint($temperatureCelsiusval, $humidityValue);
          //         if ($dewPoint == '') {
          //           $dewPoint = 10;
          //         }
          //         // Create a new sensor name for DPV and store its value
          //         $dewPointSensorName = 'DewPoint_' . ($key + 1);
          //         if (!isset($sensorValues[$dewPointSensorName])) {
          //             $sensorValues[$dewPointSensorName] = [
          //                 'type' => 'multi',
          //                 'data' => [],
          //                 'spname' => $dewPointSensorName,
          //                 'unit' => 'MA',
          //                 'color' => '#FF33C7',
          //                 'icon' => '<svg viewBox="0 0 24 24" width="50" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M15 4H20M15 8H20M17 12H20M8 15.9998C7.44772 15.9998 7 16.4475 7 16.9998C7 17.5521 7.44772 17.9998 8 17.9998C8.55228 17.9998 9 17.5521 9 16.9998C9 16.4475 8.55228 15.9998 8 15.9998ZM8 15.9998V9M8 16.9998L8.00707 17.0069M12 16.9998C12 19.209 10.2091 20.9998 8 20.9998C5.79086 20.9998 4 19.209 4 16.9998C4 15.9854 4.37764 15.0591 5 14.354L5 6C5 4.34315 6.34315 3 8 3C9.65685 3 11 4.34315 11 6V14.354C11.6224 15.0591 12 15.9854 12 16.9998Z" stroke="#FF33C7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>',
          //                 // You can add more properties here if needed
          //             ];
          //         }
          //         $sensorValues[$dewPointSensorName]['data'][] = ['x' => $changedateBycountry, 'y' => number_format($dewPoint)];
          //       }
                  
          //     }
          // }
      }

      // echo "<pre>";print_r($sensorValues);exit;

      $html = '<div class="table-responsive">';
      $html .= '<table class="table table-bordered">';
      $html .= '<thead>';
      $html .= '<tr style="text-align:center; font-family:math">';
      $html .= '<th colspan="3">Summary</th>'; // Spanning all columns for the title
      $html .= '</tr>';
      $html .= '<tr style="text-align:center; font-family:math">';
      $html .= '<th>Sensor Name</th>';
      $html .= '<th>Value</th>';
      $html .= '<th>Date</th>';
      $html .= '</tr>';
      $html .= '</thead>';
      $html .= '<tbody>';
      
      ksort($sensorValues);
      foreach ($sensorValues as $sensorName => $sensorData) {
        $sensorValueType = $sensorData['type'];
        $graphname = ChangeGraphName::where('original_name', 'like', '%' . $sensorName . '%')->where('device_id', $device_id)->where('user_id', $User_Id)->first();
        $sensorValues[$sensorName]['changename'] = $sensorName;
        $graph_name = $sensorName;
        if (!empty($graphname)) {
          $sensorValues[$sensorName]['changename'] = $graphname->change_name;
          $graph_name = $graphname->change_name;
        }
        //echo $sensorValueType;
        if ($sensorValueType == 'lastvalue') {
          $sensorValues[$sensorName]['data'] = $sensorValues[$sensorName]['data'][0];
          $sensorValues[$sensorName]['type'] = 'single';
          
          $sensorValuetbl = $sensorData['data'][0]['y'];
          $sensorDatetbl = $sensorData['data'][0]['x'];

        }else if($sensorValueType == 'location'){
          $sensorValues[$sensorName]['data'] = $sensorValues[$sensorName]['data'][0];
          $sensorValues[$sensorName]['type'] = 'location';
          $locationData = $sensorData['data'][0];
          $xValue = $locationData['x'];
          $yValue = $locationData['y'];

          $coordinates = explode(',', $yValue);

          $latitude = $coordinates[0];
          $longitude = $coordinates[1];
          $latLongStr = 'Latitude: '.$latitude.'° N'.', Longitude: '.$longitude.'° W';

          $address = $this->getAddressFromCoordinates($latitude,$longitude);

          $LocationAddress = $address->original['address'];
          $sensorValues[$sensorName]['data'] = ['x' => $xValue, 'y' => $latLongStr, 'address' => $LocationAddress,'Latitude' => $latitude, 'Longitude' => $longitude];

          $sensorValuetbl = $yValue;
          $sensorDatetbl = $xValue;
          //print_r($sensorData['data']);
        }else{
          
          $sensorValues[$sensorName]['type'] = 'multi';

          $lastDataPoint = end($sensorData['data']);

          $sensorValuetbl = $lastDataPoint['y'];
          $sensorDatetbl = $lastDataPoint['x'];

        }
        $sensorValuetbl = (float) $sensorValuetbl;
        $unit = $sensorData['unit'];
        $html .= '<tr style="text-align:center;">';
        $html .= '<td>' . $graph_name . '</td>';
        $html .= '<td>' . number_format($sensorValuetbl,2) .' '. $unit . '</td>';
        $html .= '<td>' . $sensorDatetbl . '</td>';
        $html .= '</tr>';

      }

      $html .= '</tbody>';
      $html .= '</table>';
      $html .= '</div>';  
      // echo "<pre>";print_r($sensorValues);exit;

      // Check if 'location' key exists
      /*if (isset($sensorValues['location'])) {
          // Accessing 'location' data
          $locationData = $sensorValues['location']['data'];
          $xValue = $locationData['x'];
          $yValue = $locationData['y'];

          $coordinates = explode(',', $yValue);
          $latitude = $coordinates[0];
          $longitude = $coordinates[1];
          $latLongStr = 'Latitude: '.$latitude.'° N'.', Longitude: '.$longitude.'° W';

          $address = $this->getAddressFromCoordinates($latitude,$longitude);
          $LocationAddress = $address->original['address'];
          $sensorValues['location']['data'] = ['x' => $xValue, 'y' => $latLongStr, 'address' => $LocationAddress,'Latitude' => $latitude, 'Longitude' => $longitude];
      } else {
          //echo "Location does not exist.\n";
      }*/
        $message = "success";
        $success = "success";

        $data = ['sensordata' => $sensorValues];
    }

     $responseData = ['status' => $success, 'msg' => $message, 'data' => $data, 'devide_id' => $device_id, 'sensorconfig' => $sensorConfig, 'html' => $html];
     
    //return view('content/dashboard/graph', compact('soialSensorValues'));
    return response()->json($responseData);
  }

  public function calculateDewPoint($temperatureCelsius, $relativeHumidity) {
    if (is_numeric($temperatureCelsius) && is_numeric($relativeHumidity)) {
        // Calculate the vapor pressure
        $vaporPressure = 6.112 * exp((17.67 * $temperatureCelsius) / ($temperatureCelsius + 243.5)) * ($relativeHumidity / 100);
        
        // Calculate the Dew Point Temperature (DPT)
        $dewPoint = (243.5 * log($vaporPressure / 6.112)) / (17.67 - log($vaporPressure / 6.112));
        
        return $dewPoint;
    } else {
        // Handle the case when either $temperatureCelsius or $relativeHumidity is not numeric
        return null; // Or any other appropriate action
    }
  }

  public function change_device_name(Request $request){

    $post_data = $request->all();
    //echo "<pre>"; print_r($post_data); exit();
    $user_id = $post_data['user_id'];
    $change_text = $post_data['change_text'];
    $device_id = $post_data['device_id'];

    $change_text_data = ChangeDeviceName::where('user_id', $user_id)->where('device_id', $device_id)->first();

    try {
      $change_text_data = ChangeDeviceName::where('user_id', $user_id)->where('device_id', $device_id)->first();
      //echo "<pre>"; print_r($change_text_data); die();
      if (empty($change_text_data)) {
        ChangeDeviceName::create([
            'user_id' => $user_id,
            'device_id' => $device_id,
            'change_name' => $change_text,
        ]);
      }else{
        $updateData = ['change_name'=>$change_text, "updated_at" => date('Y-m-d H:i:s')];
        ChangeDeviceName::where("user_id", $user_id)->where("device_id", $device_id)->update($updateData);
      }

      $message = "SUCCESS";
      $responseData = ['success' => 'success', 'error' => '', 'msg' => $message];
    } catch (Exception $ex) {
      $message = $ex->getMessage();
      $responseData = ['success' => 'failure', 'error' => '', 'msg' => $message];
    }

    return response()->json($responseData);

  }

  public function change_graph_name(Request $request){

    $post_data = $request->all();
    //echo "<pre>"; print_r($post_data); exit();
    $user_id = $post_data['user_id'];
    $change_text = $post_data['change_text'];
    $device_id = $post_data['device_id'];
    $original_name = $post_data['original_name'];

    try {
      $change_text_data = ChangeGraphName::where('user_id', $user_id)->where('device_id', $device_id)->where('original_name', $original_name)->first();
      //echo "<pre>"; print_r($change_text_data); die();
      if (empty($change_text_data)) {
        ChangeGraphName::create([
            'user_id' => $user_id,
            'device_id' => $device_id,
            'original_name' => $original_name,
            'change_name' => $change_text,
        ]);
      }else{
        $updateData = ['change_name'=>$change_text, "updated_at" => date('Y-m-d H:i:s')];
        ChangeGraphName::where("user_id", $user_id)->where("device_id", $device_id)->where("original_name", $original_name)->update($updateData);
      }

      $message = "SUCCESS";
      $responseData = ['success' => 'success', 'error' => '', 'msg' => $message];
    } catch (Exception $ex) {
      $message = $ex->getMessage();
      $responseData = ['success' => 'failure', 'error' => '', 'msg' => $message];
    }

    return response()->json($responseData);

  }

  public function getAddressFromCoordinates($latitude, $longitude)
    {
        // Set the Nominatim API endpoint
        $apiEndpoint = 'https://nominatim.openstreetmap.org/reverse';

        // Set parameters for the API request
        $params = [
            'format' => 'json',
            'lat' => $latitude,
            'lon' => $longitude,
        ];

        // Use Guzzle to make the API request
        $client = new Client();
        $response = $client->get($apiEndpoint, ['query' => $params]);
        $data = json_decode($response->getBody(), true);

        // Check if the response contains an address
        if (isset($data['display_name'])) {
            $address = $data['display_name'];
            return response()->json(['address' => $address]);
        } else {
            return response()->json(['error' => 'Unable to fetch address.']);
        }
    }

  public function get_show_summary_old(Request $request){

    $post_data = $request->all();
    
    $user_id = $post_data['user_id'];
    $device_id = $post_data['device_id'];

    $user_sensor_data = SensorData::where('device_id', $device_id)->get()->toArray();
    

    $highestValues = array(
        'Soil Sensor' => array('max_value' => 0, 'max_date' => null, 'min_value' => PHP_INT_MAX, 'min_date' => null),
        'Pressure Sensor' => array('max_value' => 0, 'max_date' => null, 'min_value' => PHP_INT_MAX, 'min_date' => null),
        'Humidity Sensor' => array('max_value' => 0, 'max_date' => null, 'min_value' => PHP_INT_MAX, 'min_date' => null),
        'Temperature Sensor' => array('max_value' => 0, 'max_date' => null, 'min_value' => PHP_INT_MAX, 'min_date' => null)
    );


    // Iterate over sensor data array to find highest and lowest values and their dates
    foreach ($user_sensor_data as $data) {
        // Soil Sensor
        if ($data['soilSensorValue'] > $highestValues['Soil Sensor']['max_value']) {
            $highestValues['Soil Sensor']['max_value'] = $data['soilSensorValue'].' %';
            $dateTime = new \DateTime($data['created_at']);
            $created_at = $dateTime->format('Y-m-d H:i:s');
            $highestValues['Soil Sensor']['max_date'] = $created_at;
        }
        if ($data['soilSensorValue'] < $highestValues['Soil Sensor']['min_value']) {
            $highestValues['Soil Sensor']['min_value'] = $data['soilSensorValue'].' %';
            $dateTime = new \DateTime($data['created_at']);
            $created_at = $dateTime->format('Y-m-d H:i:s');
            $highestValues['Soil Sensor']['min_date'] = $created_at;
        }
        // Pressure Sensor
        if ($data['pressureSensorValue'] > $highestValues['Pressure Sensor']['max_value']) {
            $highestValues['Pressure Sensor']['max_value'] = $data['pressureSensorValue'].' Pa';
            $dateTime = new \DateTime($data['created_at']);
            $created_at = $dateTime->format('Y-m-d H:i:s');
            $highestValues['Pressure Sensor']['max_date'] = $created_at;
        }
        if ($data['pressureSensorValue'] < $highestValues['Pressure Sensor']['min_value']) {
            $highestValues['Pressure Sensor']['min_value'] = $data['pressureSensorValue'].' Pa';
            $dateTime = new \DateTime($data['created_at']);
            $created_at = $dateTime->format('Y-m-d H:i:s');
            $highestValues['Pressure Sensor']['min_date'] = $created_at;
        }
        // Humidity Sensor
        if ($data['humiditySensorValue'] > $highestValues['Humidity Sensor']['max_value']) {
            $highestValues['Humidity Sensor']['max_value'] = $data['humiditySensorValue'].' %';
            $dateTime = new \DateTime($data['created_at']);
            $created_at = $dateTime->format('Y-m-d H:i:s');
            $highestValues['Humidity Sensor']['max_date'] = $created_at;
        }
        if ($data['humiditySensorValue'] < $highestValues['Humidity Sensor']['min_value']) {
            $highestValues['Humidity Sensor']['min_value'] = $data['humiditySensorValue'].' %';
            $dateTime = new \DateTime($data['created_at']);
            $created_at = $dateTime->format('Y-m-d H:i:s');
            $highestValues['Humidity Sensor']['min_date'] = $created_at;
        }
        // Temperature Sensor
        if ($data['temperatureSensorValue'] > $highestValues['Temperature Sensor']['max_value']) {
            $highestValues['Temperature Sensor']['max_value'] = $data['temperatureSensorValue'].' °C';
            $dateTime = new \DateTime($data['created_at']);
            $created_at = $dateTime->format('Y-m-d H:i:s');
            $highestValues['Temperature Sensor']['max_date'] = $created_at;
        }
        if ($data['temperatureSensorValue'] < $highestValues['Temperature Sensor']['min_value']) {
            $highestValues['Temperature Sensor']['min_value'] = $data['temperatureSensorValue'].' °C';
            $dateTime = new \DateTime($data['created_at']);
            $created_at = $dateTime->format('Y-m-d H:i:s');
            $highestValues['Temperature Sensor']['min_date'] = $created_at;
        }
    }

    // Generate HTML table structure
    $html = '<table class="table table-bordered">';
    $html .= '<thead>';
    $html .= '<tr style="text-align:center; font-family:math">';
    $html .= '<th>Sensor Name</th>';
    $html .= '<th>Value</th>';
    $html .= '<th>Date</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';

    // Add rows for each sensor type
    foreach ($highestValues as $sensor => $data) {
        $html .= '<tr style="text-align:center;">';
        $html .= '<td>' . $sensor . '</td>';
        $html .= '<td>' . $data['max_value'] . '</td>';
        $html .= '<td>' . $data['max_date'] . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody>';
    $html .= '</table>';

    // Return HTML table structure as part of AJAX response
    // echo $html; die();
    $responseData = ['success' => 'success', 'error' => '', 'html' => $html];
    return response()->json($responseData);


  }

  /*public function get_show_summary(Request $request){

    $authuser = Auth::user();

    $message = "Failure";
    $post_data = $request->all();
    $device_id = $post_data['device_id'];
    $user_id = $post_data['user_id'];
    if (!empty($device_id)) {
      // $sensor_data = SensorData::where('device_id', $device_id)->get()->toArray();
      $latestSensorData = SensorData::where('device_id', $device_id)->latest('created_at')->first();

      if ($latestSensorData !== null) {
          $sensor_data[] = $latestSensorData->toArray();
      } else {
          // Handle the case where no data is found
          $sensor_data = [];
      }
      
      $outputArray = [];
      $sensorValues = [];
      $sensorColors = [];
      // echo "<pre>"; print_r($sensor_data); die();
      if (empty($sensor_data)) {
        $responseData = ['success' => 'success', 'error' => '', 'html' => ""];
        return response()->json($responseData);
      }
      foreach (array_reverse($sensor_data) as $item) {
          $formattedDateTime = $item['created_at'];
          $dateTime = new \DateTime($formattedDateTime);
          $createdAt = $dateTime->format('Y-m-d H:i:s');
          $temperatureValues = [];
          $humidityValues = [];
          $changedateBycountry =  Country::changedateBytimezone($dateTime, $authuser->timezone)->format('Y-m-d H:i:s');
          // Initialize an array to store sensor values dynamically
          foreach ($item['sensor_data'] as $sensorName => $sensorDetails) {

            $sensorValueKey = $sensorName;
            $sensorValueType = isset($sensor_data[0]['sensor_data'][$sensorValueKey]['type']) ? $sensor_data[0]['sensor_data'][$sensorValueKey]['type'] : "graph" ;
            $sensorValueColor = $sensorDetails['unit'];
              // Add sensor values to the dynamically generated array
            $sensorValues[$sensorName]['type'] = $sensorValueType;
            $sensorValues[$sensorName]['data'][] = ['x' => $changedateBycountry, 'y' => $sensorDetails['value']];
              
            $sensorValues[$sensorName]['spname'] = $sensorName;
            $sensorValues[$sensorName]['unit'] = $sensorDetails['unit'];

            // if (strpos($sensorName, "AirTemperature") !== false) {
            //     $temperatureValues[] = ['value' => $sensorDetails['value'], 'type' => $sensorValueType];
            // }
            
            // if (strpos($sensorName, "Humidity") !== false) {
            //     $humidityValues[] = ['value' => $sensorDetails['value'], 'type' => $sensorValueType];
            // }

          }

      }

      foreach ($sensorValues as $sensorName => $sensorData) {
        $sensorValueType = $sensorData['type'];

        //echo $sensorValueType;
        if ($sensorValueType == 'lastvalue') {
          $sensorValues[$sensorName]['data'] = $sensorValues[$sensorName]['data'][0];
          $sensorValues[$sensorName]['type'] = 'single';
        }else if($sensorValueType == 'location'){
          $sensorValues[$sensorName]['data'] = $sensorValues[$sensorName]['data'][0];
          $sensorValues[$sensorName]['type'] = 'location';
          $locationData = $sensorData['data'][0];
          $xValue = $locationData['x'];
          $yValue = $locationData['y'];

          $coordinates = explode(',', $yValue);
          $latitude = $coordinates[0];
          $longitude = $coordinates[1];
          $latLongStr = 'Latitude: '.$latitude.'° N'.', Longitude: '.$longitude.'° W';

          $address = $this->getAddressFromCoordinates($latitude,$longitude);

          $LocationAddress = $address->original['address'];
          $sensorValues[$sensorName]['data'] = ['x' => $xValue, 'y' => $latLongStr, 'address' => $LocationAddress,'Latitude' => $latitude, 'Longitude' => $longitude];

          //print_r($sensorData['data']);
        }
        else{
          
           $sensorValues[$sensorName]['type'] = 'multi';

        }
      }

      $html = '<div class="table-responsive">';
      $html .= '<table class="table table-bordered">';
      $html .= '<thead>';
      $html .= '<tr style="text-align:center; font-family:math">';
      $html .= '<th colspan="3">Summary</th>'; // Spanning all columns for the title
      $html .= '</tr>';
      $html .= '<tr style="text-align:center; font-family:math">';
      $html .= '<th>Sensor Name</th>';
      $html .= '<th>Value</th>';
      $html .= '<th>Date</th>';
      $html .= '</tr>';
      $html .= '</thead>';
      $html .= '<tbody>';
      
      // Add rows for each sensor type
      foreach ($sensorValues as $sensorName => $sensorData) {
      // Extract value and date from sensorData array
        // echo "<pre>"; print_r($sensorData['data']['y']); die();
        if ($sensorData['type'] === 'single' || $sensorData['type'] === 'location') {
            // Handle single value
            $sensorValue = $sensorData['data']['y'];
            $sensorDate = $sensorData['data']['x'];
        } elseif ($sensorData['type'] === 'multi' && count($sensorData['data']) > 0) {
            // Handle multiple values
            $sensorValue = $sensorData['data'][0]['y'];
            $sensorDate = $sensorData['data'][0]['x'];
        }

        $graphname = ChangeGraphName::where('original_name', 'like', '%' . $sensorName . '%')->where('device_id', $device_id)->where('user_id', $user_id)->first();
        $graph_name = $sensorName;
        if (!empty($graphname)) {
          $graph_name = $graphname->change_name;
        }
        

        $unit = $sensorData['unit'];

          $html .= '<tr style="text-align:center;">';
          $html .= '<td>' . $graph_name . '</td>';
          $html .= '<td>' . number_format($sensorValue,2) .' '. $unit . '</td>';
          $html .= '<td>' . $sensorDate . '</td>';
          $html .= '</tr>';
      }

      $html .= '</tbody>';
      $html .= '</table>';
      $html .= '</div>';

    }

    
    $responseData = ['success' => 'success', 'error' => '', 'html' => $html];
    return response()->json($responseData);
  }*/

  public function fileExport_old(Request $request)
  {
      $post_data = $request->all();
      $device_id = $post_data['device_id'];
      $fromDate = $post_data['from_date'];
      $toDate = $post_data['to_date'];

      if ($fromDate == '' || $toDate == '') {
          $fromDate = '1970-01-01 00:00:00';
          $toDate = date('Y-m-d H:i:s');
      } else {
          $fromDate .= ' 00:00:00';
          $toDate .= ' 23:59:59';
      }

      $exportdata = SensorData::where('device_id', $device_id)
          ->whereBetween('created_at', [$fromDate, $toDate])
          ->get()
          ->toArray();

      if (empty($exportdata)) {
          return response()->json(['status' => 'failure', 'message' => 'No data found']);
      }

      $allHeaders = [];
      foreach ($exportdata as $row) {
          $flatRow = $this->flattenArray($row);
          $allHeaders = array_merge($allHeaders, array_keys($flatRow));
      }
      $allHeaders = array_unique($allHeaders);

      $filename = $device_id . "_data_" . date('YmdHis') . ".csv";

      $response = new StreamedResponse(function () use ($exportdata, $allHeaders) {
          $handle = fopen('php://output', 'w');

          // Write BOM for UTF-8
          fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

          fputcsv($handle, $allHeaders);

          foreach ($exportdata as $row) {
              $flattenedRow = $this->flattenArray($row);
              $csvRow = [];
              foreach ($allHeaders as $header) {
                  $csvRow[] = $flattenedRow[$header] ?? '';
              }
              fputcsv($handle, $csvRow);
          }

          fclose($handle);
      });

      $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
      $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

      return $response;
  }

  public function fileExport(Request $request)
  {
      $post_data = $request->all();
      $device_id = $post_data['device_id'];
      $fromDate = $post_data['from_date'];
      $toDate = $post_data['to_date'];

      // Set default date range if not provided
      if ($fromDate == '' || $toDate == '') {
          // Set default range to retrieve all data
          $fromDate = '1970-01-01 00:00:00';
          $toDate = date('Y-m-d H:i:s');
      } else {
          // Concatenate time for start and end of the day
          $fromDate .= ' 00:00:00';
          $toDate .= ' 23:59:59';
      }

      // Retrieve data based on device ID and date range
      $exportdata = SensorData::where('device_id', $device_id)
          ->whereBetween('created_at', [$fromDate, $toDate])
          ->orderBy('_id', 'desc')
          ->get()
          ->toArray();
       // echo "<pre>"; print_r($exportdata); die();    
      // Check if data is empty
      if (empty($exportdata)) {
          return response()->json(['status' => 'failure', 'message' => 'No data found']);
      }
      $allHeaders = [];
      foreach ($exportdata as $row) {
          $flatRow = $this->flattenArray($row);
          $allHeaders = array_merge($allHeaders, array_keys($flatRow));
      }
      $allHeaders = array_unique($allHeaders);

      // Ensure 'created_at' and 'updated_at' are the last columns
      if (($createdAtKey = array_search('created_at', $allHeaders)) !== false) {
          unset($allHeaders[$createdAtKey]);
          $allHeaders[] = 'created_at';
      }
      if (($updatedAtKey = array_search('updated_at', $allHeaders)) !== false) {
          unset($allHeaders[$updatedAtKey]);
          $allHeaders[] = 'updated_at';
      }

      $filename = $device_id . "_data_" . date('YmdHis') . ".csv";

      $response = new StreamedResponse(function () use ($exportdata, $allHeaders) {
          $handle = fopen('php://output', 'w');

          // Write BOM for UTF-8
          fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

          fputcsv($handle, $allHeaders);

          foreach ($exportdata as $row) {
              $flattenedRow = $this->flattenArray($row);
              $csvRow = [];
              foreach ($allHeaders as $header) {
                  $csvRow[] = $flattenedRow[$header] ?? '';
              }
              fputcsv($handle, $csvRow);
          }

          fclose($handle);
      });

      $response->headers->set('Content-Type', 'text/csv');
      $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

      return $response;

      // Set the filename for the CSV file
      // $filename = $device_id . "_data_" . date('YmdHis') . ".csv";

      // // Create a StreamedResponse to output CSV data directly
      // $response = new StreamedResponse(function () use ($exportdata) {
      //     $handle = fopen('php://output', 'w');

      //     // Get the keys for the header from the first flattened row
      //     $headerWritten = false;
      //     // Write CSV header
      //     foreach ($exportdata as $row) {
      //         $flattened_row = $this->flatten_array($row);

      //         // Write the header if not written yet
      //         if (!$headerWritten) {
      //             fputcsv($handle, array_keys($flattened_row));
      //             $headerWritten = true;
      //         }

      //         // Write the data row
      //         fputcsv($handle, $flattened_row);
      //     }

      //     fclose($handle);
      // });

      // // Set response headers for file download
      // $response->headers->set('Content-Type', 'text/csv');
      // $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

      // // Return success response
      // return $response;
  }

  public function flattenArray($array, $prefix = '') {
      $result = [];
      foreach ($array as $key => $value) {
          $newKey = $prefix === '' ? $key : $prefix . '_' . $key;
          if (is_array($value)) {
              $result = array_merge($result, $this->flattenArray($value, $newKey));
          } else {
              $result[$newKey] = $value;
          }
      }
      return $result;
  }

  function cleanData(&$str)
  {
      if ($str == 't') $str = 'TRUE';
      if ($str == 'f') $str = 'FALSE';
      if (preg_match("/^0/", $str) || preg_match("/^\+?\d{8,}$/", $str) || preg_match("/^\d{4}.\d{1,2}.\d{1,2}/", $str) || preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$str)) {
          $str = " $str";
      }
      if (strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
  }

  public function updateAlarm(Request $request){

    $settings = $request->input('settings');

    $user_id = $request->input('user_id');
    $device_id = $request->input('device_id');
    $allSensorData = [];
      foreach ($settings as $setting) {
          // Update or create the setting in the database
        $sensorName = $setting['sensorName'];
        $minValue = $setting['minValue'];
        $maxValue = $setting['maxValue'];

        $sensorData = [
            'sensor_name' => $sensorName,
            'min_value' => $minValue,
            'max_value' => $maxValue
        ];

        $allSensorData[] = $sensorData;
          
      }

      $alarmData = json_encode($allSensorData);

      Setalarm::updateOrCreate(
          ['sensor_name' => $sensorName, 'user_id' => $user_id, 'device_id' => $device_id],
          ['alarmdata' => $alarmData]
      );
      // die();
      // return response()->json(['success' => true]);
      $responseData = ['success' => 'success', 'error' => ''];
      return response()->json($responseData);

  }

  public function get_alarm_history(Request $request){

    $message = "Failure";
    $post_data = $request->all();
    $device_id = $post_data['device_id'];
    $user_id = $post_data['user_id'];
    if (!empty($device_id)) {
      // $sensor_data = SensorData::where('device_id', $device_id)->get()->toArray();
      $alarm_history = AlarmHistory::where('device_id', $device_id)->where('user_id', $user_id)->limit(15)->get()->toArray();
      
    } else {

      $responseData = ['success' => 'failure', 'error' => '', 'html' => ''];
      return response()->json($responseData);

    }

    $html = '<div class="table-responsive" style="margin-top: 10px;">';
    $html .= '<table class="table table-bordered">';
    $html .= '<thead>';
    $html .= '<tr style="text-align:center; font-family:math">';
    $html .= '<th colspan="4">Alarm History</th>'; // Spanning all columns for the title
    $html .= '</tr>';
    $html .= '<tr style="text-align:center; font-family:math">';
    $html .= '<th>Alarm Date</th>';
    $html .= '<th>Sensor Name</th>';
    $html .= '<th>Alarm Value</th>';
    $html .= '<th>Actual Value</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';

    if (!empty($alarm_history)) {
      foreach ($alarm_history as $key => $history) {
        $dateTime = new \DateTime($history['created_at']);
        $createdAt = $dateTime->format('Y-m-d H:i:s');

          $html .= '<tr style="text-align:center;">';
          $html .= '<td>' . $createdAt . '</td>';
          $html .= '<td>' . $history['sensorname'] . '</td>';
          $html .= '<td>' . $history['alarmvalue'] . '</td>';
          $html .= '<td>' . $history['actualvalue'] . '</td>';
          $html .= '</tr>';
      }

      $html .= '</tbody>';
      $html .= '</table>';
      $html .= '</div>';
      $responseData = ['success' => 'success', 'error' => '', 'html' => $html];
      return response()->json($responseData);
    }else{
      $responseData = ['success' => 'failure', 'error' => '', 'html' => $html];
      return response()->json($responseData);
    }
    
  }

  public function get_alarm_data_by_sensorname(Request $request){

      $message = "Failure";
      $post_data = $request->all();
      // echo "<pre>"; print_r($post_data); die();
      $device_id = $post_data['device_id'];
      $sensorName = $post_data['sensorName'];
      $user_id = $post_data['user_id'];
      $html = ''; // Initialize HTML variable
      if (!empty($sensorName)) {
          $alarms = Setalarm::where('device_id', $device_id)
                                 ->where('user_id', $user_id)
                                 ->where('sensor_name', $sensorName)
                                 ->first(); // Use first() instead of get()->first()
          
          if (!empty($alarms)) {
              $alarmdata = json_decode($alarms->alarmdata);
              if (!empty($alarmdata)) {
                  foreach ($alarmdata as $sensorData) {
                      // $html .= '<tr>';
                      $html .= '<td><font style="vertical-align: inherit;"><span class="Sensor_Name">' . $alarms->sensor_name . '</span></font>';
                      $html .= '<input type="hidden" name="sname" value="' . $alarms->sensor_name . '" class="sname"></td>';
                      $html .= '<td class="min-td"><input type="number" value="' . $sensorData->min_value . '" class="form-control min-value" /></td>';
                      $html .= '<td class="max-td"><input type="number" value="' . $sensorData->max_value . '" class="form-control max-value" /></td>';
                      // $html .= '</tr>';
                  }
                $responseData = ['success' => 'success', 'error' => '', 'html' => $html];
              }else{
                $mintd = '<input type="number" value="" class="form-control min-value" />';
                $maxtd = '<input type="number" value="" class="form-control max-value" />';
                $responseData = ['success' => 'failure', 'error' => '', 'mintd' => $mintd, 'maxtd' => $maxtd];
              }
          }else{
            $mintd = '<input type="number" value="" class="form-control min-value" />';
            $maxtd = '<input type="number" value="" class="form-control max-value" />';
            $responseData = ['success' => 'failure', 'error' => '', 'mintd' => $mintd, 'maxtd' => $maxtd];
          }
      }else{
        $mintd = '<input type="number" value="" class="form-control min-value" />';
        $maxtd = '<input type="number" value="" class="form-control max-value" />';
        $responseData = ['success' => 'failure', 'error' => '', 'mintd' => $mintd, 'maxtd' => $maxtd];
       // Return HTML in JSON response
      }
      return response()->json($responseData);
  }

  public function savelatlong(Request $request){

    $post_data = $request->all();
    // echo "<pre>"; print_r($post_data); die();
    $device_id = $post_data['device_id'];
    $user_id = $post_data['user_id'];
    $LAT = $post_data['LAT'];
    $LON = $post_data['LON'];

    try {
      $get_lat_long = SetLatLong::where('user_id', $user_id)->where('device_id', $device_id)->first();
      //echo "<pre>"; print_r($change_text_data); die();
      if (empty($get_lat_long)) {
        SetLatLong::create([
            'user_id' => $user_id,
            'device_id' => $device_id,
            'latitude' => $LAT,
            'longitude' => $LON,
        ]);
        $message = "Location Add Successfully!";
      }else{
        $updateData = ['latitude'=>$LAT,'longitude'=>$LON, "updated_at" => date('Y-m-d H:i:s')];
        SetLatLong::where("user_id", $user_id)->where("device_id", $device_id)->update($updateData);
        $message = "Location Update Successfully!";
      }
      
      $responseData = ['success' => 'success', 'error' => '', 'msg' => $message];
    } catch (Exception $ex) {
      $message = $ex->getMessage();
      $responseData = ['success' => 'failure', 'error' => '', 'msg' => $message];
    }

    return response()->json($responseData);

  }

  public function saveattributes(Request $request){

    $post_data = $request->all();
    // echo "<pre>"; print_r($post_data); die();

    try {
      
      $attributeValue = [];
      foreach ($post_data['attributes'] as $attribute) {
          $attributeValue[$attribute['key']] = $attribute['value'];
      }

      $attributeData = [
          'user_id' => $post_data['user_id'],
          'device_id' => $post_data['device_id'],
          'attributes' => json_encode($attributeValue), // Assuming 'attribute' in $data is an array or object
      ];

      $existingAttribute = Attributes::where('user_id', $post_data['user_id'])->where('device_id', $post_data['device_id'])->first();
            
      if ($existingAttribute) {
          // Update the existing record
          $existingAttribute->update($attributeData);
          $message = "Attributes Updated Successfully!";
      } else {
          // Create a new record
          Attributes::create($attributeData);
          $message = "Attributes Added Successfully!";
      }
      
      $responseData = ['success' => 'success', 'error' => '', 'msg' => $message];
    } catch (Exception $ex) {
      $message = $ex->getMessage();
      $responseData = ['success' => 'failure', 'error' => '', 'msg' => $message];
    }

    return response()->json($responseData);


    

  }

}
