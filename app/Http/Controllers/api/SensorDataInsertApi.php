<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Models\User;
use App\Models\SensorData;
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

    public function sensordatastore(Request $request){
        $data = $request->all();
        $data['updated_at'] = '';
        $data['created_at'] = date('Y-m-d H:i:s');
        if(isset( $request['device_id'] )){ 
            $device_id = $request['device_id'];
            $data['device_id'] = (string) $request['device_id'];
        }
        // echo "<pre>"; print_r($request->all()); die();
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

    public function getsensordata(){
        $sensordata = SensorData::orderBy('created_at', 'desc')->get()->toArray();
        
        echo "<pre>"; print_r($sensordata); exit();
        $sensordata = json_encode($sensordata);
        var_dump($sensordata); die();
    }

}
