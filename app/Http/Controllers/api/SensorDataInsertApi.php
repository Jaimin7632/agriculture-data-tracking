<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Models\User;
use App\Models\SensorData;
use App\Models\Setalarm;
use App\Jobs\SendEmailJob;
use DB;

class SensorDataInsertApi extends Controller
{

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

    public function sensordatastore_old(Request $request){
        $data = $request->all();
        $data['updated_at'] = '';
        $data['created_at'] = date('Y-m-d H:i:s');
        $userdata = [];
        if(isset( $request['device_id'] )){ 
            $device_id = $request['device_id'];
            $data['device_id'] = (string) $request['device_id'];
            $userdata = User::where('device_id', 'like', "%$device_id%")->get()->toArray();


        }

        if (!empty($userdata)) {
            foreach ($userdata as $key => $value) {
               $uid = $value['_id'];
               $uname = $value['name'];
               $alarmdata = Setalarm::where('user_id', $uid)->where('device_id', $device_id)->get()->first();
                if (!empty($alarmdata)) {
                   $alarm_data = json_decode($alarmdata->alarmdata);                    
                   if (!empty($alarm_data)) {
                        $dataObject = (object) $data;
                        foreach ($alarm_data as $key => $sensorData) {
                            if (property_exists($dataObject, $sensorData->sensor_name)) {
                                $sensorvalue = $dataObject->{$sensorData->sensor_name};
                                // echo $sensorvalue; die();
                                if ($sensorData->min_value != '') {
                                    if ($sensorvalue < $sensorData->min_value) {
                                    echo "Send Mail To User: " .$sensorData->sensor_name. " Min Value Set is ".$sensorData->min_value.'<br>';
                                    }
                                }
                                
                                if ($sensorData->max_value != '') {
                                    if ($sensorvalue > $sensorData->max_value) {
                                    echo "Send Mail To User: " .$sensorData->sensor_name. " Max Value Set is ".$sensorData->max_value.'<br>';
                                    }
                                }
                                
                            }
                        }
                    }
                }
            }
        }
        
        try {
            // Your API logic here
            $insertData = DB::collection('sensor_data')->insert($data);
            if($insertData){
                return response()->json(['status'=>'success','data'=>$data,'message' => 'Request was successful'], 200);
            }else{
                return response()->json(['status'=>'failed','data'=>'','message' => 'Request failed'], 201);
            }
            // If the API request is successful, you can return a success response.
            
        } catch (\Exception $e) {
            // If there is an error or the request fails, you can return an error response.
            return response()->json(['status'=>'failed','data'=>'','error' => 'Request failed', 'message' => $e->getMessage()], 500);
        }

    }

    public function sensordatastore(Request $request){
        try {
            $data = $request->all();
            $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s');
            
            if(isset($data['device_id'])) { 
                // Bulk insert data into MongoDB
                $insertData = DB::collection('sensor_data')->insert([$data]);
                
                $device_id = $data['device_id'];
                $data['device_id'] = (string) $device_id;
                $userdata = User::where('device_id', 'like', "%$device_id%")->get();

                foreach ($userdata as $user) {
                    $uid = $user['_id'];
                    $alarmdata = Setalarm::where('user_id', $uid)->where('device_id', $device_id)->first();
                    
                    if (!empty($alarmdata)) {
                        $alarm_data = json_decode($alarmdata->alarmdata);                    

                        foreach ($alarm_data as $sensorData) {
                            if (isset($data[$sensorData->sensor_name])) {
                                $sensorvalue = $data[$sensorData->sensor_name];

                                if ($sensorData->min_value != '' && $sensorvalue < $sensorData->min_value) {
                                    // echo "Send Mail To User: " .$user['name']. ", " .$sensorData->sensor_name. " Min Value Set is ".$sensorData->min_value.'<br>';
                                    //SendEmailJob::dispatch($user['email'], "Min Value Alert - {$sensorData->sensor_name}", "Min value set is {$sensorData->min_value}");
                                    SendEmailJob::dispatch($user, $sensorData->sensor_name, $sensorData->min_value, $sensorData->max_value);
                                }

                                if ($sensorData->max_value != '' && $sensorvalue > $sensorData->max_value) {
                                    // echo "Send Mail To User: " .$user['name']. ", " .$sensorData->sensor_name. " Max Value Set is ".$sensorData->max_value.'<br>';
                                    //SendEmailJob::dispatch($user['email'], "Max Value Alert - {$sensorData->sensor_name}", "Max value set is {$sensorData->max_value}");
                                    SendEmailJob::dispatch($user, $sensorData->sensor_name, $sensorData->min_value, $sensorData->max_value);

                                }
                            }
                        }
                    }
                }
            }

            

            if($insertData){
                return response()->json(['status'=>'success','data'=>$data,'message' => 'Request was successful'], 200);
            } else {
                return response()->json(['status'=>'failed','data'=>'','message' => 'Request failed'], 201);
            }
        } catch (\Exception $e) {
            return response()->json(['status'=>'failed','data'=>'','error' => 'Request failed', 'message' => $e->getMessage()], 500);
        }
    }


    public function getsensordata(){
        $sensordata = SensorData::orderBy('created_at', 'desc')->get()->toArray();
        
        echo "<pre>"; print_r($sensordata); exit();
        $sensordata = json_encode($sensordata);
        var_dump($sensordata); die();
    }

}
