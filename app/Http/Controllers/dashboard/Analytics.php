<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jenssegers\Mongodb\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use App\Models\ChangeDeviceName;
use App\Models\User;
use App\Models\SensorData;
use Carbon\Carbon;
use DB;

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
    $message = "Failure";
    $sensor_data = [];
    $soialSensorValues = [];
    $pressureSensorValues = [];
    $humiditySensorValues = [];
    $temperatureSensorValues = [];
    $post_data = $request->all();
     
    $device_id = $post_data['device_id'];
    if (!empty($device_id)) {
      $sensor_data = DB::table('sensor_data')
       ->where('device_id', $device_id)
       ->get()->toArray();
      //echo "<pre>"; print_r($sensor_data); die();
      $outputArray = [];

      $sensorConfig = [
          'soilSensor' => 'soilSensorValue',
          'pressureSensor' => 'pressureSensorValue',
          'humiditySensor' => 'humiditySensorValue',
          'temperatureSensor' => 'temperatureSensorValue',
          'temperatureSensorasas' => 'temperatureSensorValue',
          // Add more sensors as needed
      ];

      $sensorConfig = [
          'soilSensor' => ['key' => 'soilSensorValue', 'type' => 'multi', 'color' => '#FF5733'],
          'pressureSensor' => ['key' => 'pressureSensorValue', 'type' => 'multi', 'color' => '#33FF57'],
          'humiditySensor' => ['key' => 'humiditySensorValue', 'type' => 'multi', 'color' => '#5733FF'],
          'temperatureSensor' => ['key' => 'temperatureSensorValue', 'type' => 'multi', 'color' => '#FF33C7'],
          // 'newSensor' => ['key' => 'temperatureSensorValue', 'type' => 'single'],
          // Add more sensors as needed
      ];

      foreach ($sensor_data as $item) {
          $createdAt = $item['created_at'];

          // Initialize an array to store sensor values dynamically
          $sensorValues = [];
          $sensorColors = [];

          // Iterate over the sensor configuration
          /*foreach ($sensorConfig as $sensorName => $sensorValueKey) {

            $sensorValueKey = $sensorValueKey['type'];
            $sensorValueType = $sensorValueKey['key'];
            echo "<pre>"; print_r($sensorValueKey); die();
              // Add sensor values to the dynamically generated array
              // $sensorValues[$sensorName][] = ['x' => $createdAt, 'y' => $item[$sensorValueKey]];
              $sensorValues[$sensorName][] = ['x' => $createdAt, 'y' => $item[$sensorValueKey], 'type' => $sensorValueType];
              // $sensorValues[$sensorDetails['type']][$sensorName][] = ['x' => $createdAt, 'y' => $item[$sensorValueKey]];
          }*/

          foreach ($sensorConfig as $sensorName => $sensorDetails) {
              $sensorValueKey = $sensorDetails['key'];
              $sensorValueType = $sensorDetails['type'];
              $sensorValueColor = $sensorDetails['color'];

              // Add sensor values to the dynamically generated array
              $sensorValues[$sensorName]['data'][] = ['x' => $createdAt, 'y' => $item[$sensorValueKey]];
              $sensorValues[$sensorName]['color'] = $sensorValueColor;
          }

          // Now, $sensorValues contains values for all sensors dynamically
          // You can access values using $sensorValues['soilSensor'], $sensorValues['pressureSensor'], etc.
      }

        // echo "<pre>"; print_r($sensorValues); die();

      foreach ($sensor_data as $item) {
           $createdAt = $item['created_at'];

          // Soial Sensor
          $soialSensorValues[] = ['x' => $createdAt, 'y' => $item['soilSensorValue']];

          // Pressure Sensor
          $pressureSensorValues[] = ['x' => $createdAt, 'y' => $item['pressureSensorValue']];

          // Humidity Sensor
          $humiditySensorValues[] = ['x' => $createdAt, 'y' => $item['humiditySensorValue']];

          // Temperature Sensor
          $temperatureSensorValues[] = ['x' => $createdAt, 'y' => $item['temperatureSensorValue']];
      }

      
        $message = "success";
        $success = "success";
        // $data = ['soialSensorValues' => $soialSensorValues, 'pressureSensorValues' => $pressureSensorValues, 'humiditySensorValues' => $humiditySensorValues, 'temperatureSensorValues' => $temperatureSensorValues];

        $data = ['sensordata' => $sensorValues];
    }
    //  echo "<pre>"; print_r($data); die();
     $responseData = ['status' => $success, 'msg' => $message, 'data' => $data, 'devide_id' => $device_id];

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


}
