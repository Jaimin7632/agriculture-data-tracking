<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jenssegers\Mongodb\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
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
       return view('content/dashboard/dashboards-analytics', compact('user'));
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

      foreach ($sensor_data as $item) {
           $createdAt = $item['created_at'];

          // // Create an array for sensor values
          // $sensorValues = [
          //     'soialsensorvalue' => $item['soialsensorvalue'],
          //     'pressuresensorvalue' => $item['pressuresensorvalue'],
          //     'humiditysensorvalue' => $item['humiditysensorvalue'],
          //     'tempretureensorvalue' => $item['tempretureensorvalue'],
          // ];

          // $leadData['x']  = $createdAt;
          // $leadData['y']  = $item['soialsensorvalue'];
          // $soialSensorValues[]    = $leadData;

          // $leadData1['x']  = $createdAt;
          // $leadData1['y']  = $item['pressuresensorvalue'];
          // $pressureSensorValues[]    = $leadData1;

          // $leadData2['x']  = $createdAt;
          // $leadData2['y']  = $item['humiditysensorvalue'];
          // $humiditySensorValues[]    = $leadData2;

          // $leadData3['x']  = $createdAt;
          // $leadData3['y']  = $item['tempretureensorvalue'];
          // $temperatureSensorValues[]    = $leadData3;

          // Add the sensor values to the output array using created_at as the key
          // $outputArray[$createdAt] = $sensorValues;

          // Soial Sensor
          $soialSensorValues[] = ['x' => $createdAt, 'y' => $item['soialsensorvalue']];

          // Pressure Sensor
          $pressureSensorValues[] = ['x' => $createdAt, 'y' => $item['pressuresensorvalue']];

          // Humidity Sensor
          $humiditySensorValues[] = ['x' => $createdAt, 'y' => $item['humiditysensorvalue']];

          // Temperature Sensor
          $temperatureSensorValues[] = ['x' => $createdAt, 'y' => $item['tempretureensorvalue']];
      }

      // echo "<pre>"; print_r($soialSensorValues); die();

      // foreach ($outputArray as $createdAt => $sensorValues) {
      //     // Extract values for each sensor type
      //     $soialSensorValues[$createdAt] = $sensorValues['soialsensorvalue'];
      //     $pressureSensorValues[$createdAt] = $sensorValues['pressuresensorvalue'];
      //     $humiditySensorValues[$createdAt] = $sensorValues['humiditysensorvalue'];
      //     $temperatureSensorValues[$createdAt] = $sensorValues['tempretureensorvalue'];
      // }
      
        $message = "success";
        $success = "success";
        $data = ['soialSensorValues' => $soialSensorValues, 'pressureSensorValues' => $pressureSensorValues, 'humiditySensorValues' => $humiditySensorValues, 'temperatureSensorValues' => $temperatureSensorValues];
    }
    //  echo "<pre>"; print_r($data); die();
     $responseData = ['status' => $success, 'msg' => $message, 'data' => $data, 'devide_id' => $device_id];

    //return view('content/dashboard/graph', compact('soialSensorValues'));
    return response()->json($responseData);


  }


}
