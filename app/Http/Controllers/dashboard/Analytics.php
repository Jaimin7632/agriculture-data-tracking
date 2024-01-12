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

      $sensorConfig = config('global');

      foreach ($sensor_data as $item) {
          $createdAt = $item['created_at'];

          // Initialize an array to store sensor values dynamically
          $sensorValues = [];
          $sensorColors = [];

          foreach ($sensorConfig as $sensorName => $sensorDetails) {
              $sensorValueKey = $sensorDetails['key'];
              $sensorValueType = $sensorDetails['type'];
              $sensorValueColor = $sensorDetails['color'];

              if ($sensorValueType == 'single') {
                $sensorValues[$sensorName]['data'] = ['x' => $createdAt, 'y' => $item[$sensorValueKey]];
              }else{
                $sensorValues[$sensorName]['data'][] = ['x' => $createdAt, 'y' => $item[$sensorValueKey]];
              }
              // Add sensor values to the dynamically generated array
              $sensorValues[$sensorName]['color'] = $sensorValueColor;
          }

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


}
