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
    if (!empty($device_id)) {
       // echo $post_data['device_id']; die();
      $sensor_data = SensorData::where('device_id', $device_id)->get()->toArray();

      $outputArray = [];

      $sensorConfig = config('global');
      $sensorValues = [];
      $sensorColors = [];
      foreach ($sensor_data as $item) {
          $formattedDateTime = $item['created_at'];
          $dateTime = new \DateTime($formattedDateTime);
          $createdAt = $dateTime->format('Y-m-d H:i:s');

          $changedateBycountry =  Country::changedateBytimezone($createdAt, $authuser->timezone);
          // Initialize an array to store sensor values dynamically

          foreach ($sensorConfig as $sensorName => $sensorDetails) {
              $sensorValueKey = $sensorDetails['key'];
              $sensorValueType = $sensorDetails['type'];
              $sensorValueColor = $sensorDetails['color'];

              if (array_key_exists($sensorValueKey, $item)) {
                if ($sensorValueType == 'single') {
                  $sensorValues[$sensorName]['data'] = ['x' => $changedateBycountry, 'y' => $item[$sensorValueKey]];
                }else{
                  $sensorValues[$sensorName]['data'][] = ['x' => $changedateBycountry, 'y' => $item[$sensorValueKey]];
                }
                // Add sensor values to the dynamically generated array
                $sensorValues[$sensorName]['color'] = $sensorValueColor;
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

          $address = $this->getAddressFromCoordinates($latitude,$longitude);
          $LocationAddress = $address->original['address'];
          $sensorValues['location']['data'] = ['x' => $xValue, 'y' => $yValue, 'address' => $LocationAddress];
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


}
