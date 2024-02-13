<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jenssegers\Mongodb\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use App\Models\ChangeDeviceName;
use App\Models\User;
use App\Models\SensorData;
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
              ->get()
              ->toArray();
      } else {
          $sensor_data = SensorData::where('device_id', $device_id)
              ->get()
              ->toArray();
      }
      // $sensor_data = SensorData::where('device_id', $device_id)->whereBetween('created_at', [$fromDate, $toDate])->get()->toArray();

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

}
