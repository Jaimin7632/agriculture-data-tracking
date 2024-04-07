<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jenssegers\Mongodb\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use App\Models\ChangeDeviceName;
use App\Models\User;
use App\Models\SensorData;
use App\Models\Setalarm;
use App\Models\AlarmHistory;
use App\Models\Country;
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

    $device_id = $post_data['device_id'];
    $fromDate = $post_data['from_date'];
    $toDate = $post_data['to_date'];
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
      } else {
          $sensor_data = SensorData::where('device_id', $device_id)->orderByDesc('created_at')->limit(15)
              ->get()
              ->toArray();
      }
      // $sensor_data = SensorData::where('device_id', $device_id)->whereBetween('created_at', [$fromDate, $toDate])->get()->toArray();
      // echo "<pre>"; print_r($sensor_data); die();
      $outputArray = [];
      $sensorConfig = config('global');
      $sensorValues = [];
      $sensorColors = [];
      foreach ($sensor_data as $item) {
          $formattedDateTime = $item['created_at'];
          $dateTime = new \DateTime($formattedDateTime);
          $createdAt = $dateTime->format('Y-m-d H:i:s');
          // echo "<pre>"; print_r($item['sensor_data']); die;
          $changedateBycountry =  Country::changedateBytimezone($dateTime, $authuser->timezone)->format('Y-m-d H:i:s');
          // Initialize an array to store sensor values dynamically
          foreach ($item['sensor_data'] as $sensorName => $sensorDetails) {
             
              $sensorValueKey = $sensorName;
              $sensorValueType = isset($sensorDetails['type']) ? $sensorDetails['type'] : "graph" ;
              $sensorValueColor = $sensorDetails['unit'];
                // Add sensor values to the dynamically generated array
                $sensorValues[$sensorName]['type'] = $sensorValueType;
                $sensorValues[$sensorName]['data'][] = ['x' => $changedateBycountry, 'y' => $sensorDetails['value']];
                $sensorValues[$sensorName]['spname'] = $sensorName;
                $sensorValues[$sensorName]['unit'] = $sensorDetails['unit'];
                $sensorValues[$sensorName]['color'] = '#E31A1A';
                $sensorValues[$sensorName]['icon'] = '<svg viewBox="0 0 24 24" width="50" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M15 4H20M15 8H20M17 12H20M8 15.9998C7.44772 15.9998 7 16.4475 7 16.9998C7 17.5521 7.44772 17.9998 8 17.9998C8.55228 17.9998 9 17.5521 9 16.9998C9 16.4475 8.55228 15.9998 8 15.9998ZM8 15.9998V9M8 16.9998L8.00707 17.0069M12 16.9998C12 19.209 10.2091 20.9998 8 20.9998C5.79086 20.9998 4 19.209 4 16.9998C4 15.9854 4.37764 15.0591 5 14.354L5 6C5 4.34315 6.34315 3 8 3C9.65685 3 11 4.34315 11 6V14.354C11.6224 15.0591 12 15.9854 12 16.9998Z" stroke="#FF33C7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>';
              
          }
      }

      foreach ($sensorValues as $sensorName => $sensorData) {
          $sensorValueType = $sensorData['type'];
          
          if ($sensorValueType == 'lastvalue' || $sensorValueType == 'location') {
              $type = 'single';
            if ($sensorValueType == 'location') {
              $type = 'location';
            }
              // Check if the 'data' array is not empty
              if (!empty($sensorValues[$sensorName]['data'])) {
                  // Get the last element of the 'data' array
                  $lastData = end($sensorValues[$sensorName]['data']);
                  if ($lastData !== false) {
                      $sensorValues[$sensorName]['data'] = $lastData;
                      $sensorValues[$sensorName]['type'] = $type;
                  } else {
                      // Handle the case when 'data' array is empty or end() fails
                      // For example, set a default value or do something else
                      $sensorValues[$sensorName]['type'] = $type;
                  }
              } else {
                  // Handle the case when 'data' array is empty
                  // For example, set a default value or do something else
                  $sensorValues[$sensorName]['type'] = $type;
              }
          } else {
              $sensorValues[$sensorName]['type'] = 'multi';
          }
      } 
      
      // foreach ($sensorValues as $sensorName => $sensorData) {
      //   $sensorValueType = $sensorData['type'];
      //   //echo $sensorValueType;
      //   if ($sensorValueType == 'lastvalue' || $sensorValueType == 'location') {
      //     $sensorValues[$sensorName]['data'] = $sensorValues[$sensorName]['data'][-1];
      //     $sensorValues[$sensorName]['type'] = 'single';
      //   }
      //   else{
          
      //      $sensorValues[$sensorName]['type'] = 'multi';
      //   }
      // } 
      

      // Check if 'location' key exists
      if (isset($sensorValues['location'])) {
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
      }
        $message = "success";
        $success = "success";

        $data = ['sensordata' => $sensorValues];
    }

     $responseData = ['status' => $success, 'msg' => $message, 'data' => $data, 'devide_id' => $device_id, 'sensorconfig' => $sensorConfig];
     
    //return view('content/dashboard/graph', compact('soialSensorValues'));
    return response()->json($responseData);
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

  public function get_show_summary(Request $request){

    $authuser = Auth::user();

    $message = "Failure";
    $post_data = $request->all();
    $device_id = $post_data['device_id'];
    if (!empty($device_id)) {
      // $sensor_data = SensorData::where('device_id', $device_id)->get()->toArray();
      $sensor_data[] = SensorData::where('device_id', $device_id)->latest('created_at')->first()->toArray();
      
      $outputArray = [];

      $sensorConfig = config('global');
      $sensorValues = [];
      $sensorColors = [];
      foreach ($sensor_data as $item) {
          $formattedDateTime = $item['created_at'];
          $dateTime = new \DateTime($formattedDateTime);
          $createdAt = $dateTime->format('Y-m-d H:i:s');

          $changedateBycountry =  Country::changedateBytimezone($dateTime, $authuser->timezone)->format('Y-m-d H:i:s');
          // Initialize an array to store sensor values dynamically

          foreach ($sensorConfig as $sensorName => $sensorDetails) {
              $sensorValueKey = $sensorDetails['key'];
              $sensorValueType = $sensorDetails['type'];
              $sensorValueColor = $sensorDetails['color'];
              $sensorValueIcon = $sensorDetails['icon'];

              if (array_key_exists($sensorValueKey, $item)) {
                if ($sensorValueType == 'single') {
                  $sensorValues[$sensorName]['data'] = ['x' => $changedateBycountry, 'y' => $item[$sensorValueKey]];
                }else{
                  $sensorValues[$sensorName]['data'][] = ['x' => $changedateBycountry, 'y' => $item[$sensorValueKey]];
                }
                // Add sensor values to the dynamically generated array
                $sensorValues[$sensorName]['color'] = $sensorValueColor;
                $sensorValues[$sensorName]['icon'] = $sensorValueIcon;
              }
          }
      }

      // Check if 'location' key exists
      if (isset($sensorValues['location'])) {
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
          $sensorValues['location']['data'][0] = ['x' => $xValue, 'y' => $LocationAddress, 'address' => $LocationAddress];
      }

    }

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
    foreach ($sensorValues as $sensorName => $sensorData) {
    // Extract value and date from sensorData array
        $sensorValue = $sensorData['data'][0]['y'];
        $sensorDate = $sensorData['data'][0]['x'];

        $html .= '<tr style="text-align:center;">';
        $html .= '<td>' . $sensorName . '</td>';
        $html .= '<td>' . $sensorValue . '</td>';
        $html .= '<td>' . $sensorDate . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody>';
    $html .= '</table>';
    $responseData = ['success' => 'success', 'error' => '', 'html' => $html];
    return response()->json($responseData);
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
          ->get()
          ->toArray();

      // Check if data is empty
      if (empty($exportdata)) {
          return response()->json(['status' => 'failure', 'message' => 'No data found']);
      }

      // Set the filename for the CSV file
      $filename = $device_id . "_data_" . date('YmdHis') . ".csv";

      // Create a StreamedResponse to output CSV data directly
      $response = new StreamedResponse(function () use ($exportdata) {
          $handle = fopen('php://output', 'w');

          // Write CSV header
          fputcsv($handle, array_keys($exportdata[0]));

          // Write CSV data
          foreach ($exportdata as $row) {
              fputcsv($handle, $row);
          }

          fclose($handle);
      });

      // Set response headers for file download
      $response->headers->set('Content-Type', 'text/csv');
      $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

      // Return success response
      return $response;
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

}
