<?php

namespace App\Http\Controllers\user_management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jenssegers\Mongodb\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Models\User;
use App\Models\Timezones;
use App\Models\Attributes;

class UserManagementController extends Controller
{

  use RegistersUsers;

  protected $redirectTo = '/';

	public function __construct()
    {
        $this->middleware('auth');
    }
    
  public function user_list()
  {
    // return view('content.dashboard.dashboards-analytics');
    // $user = Auth::user();
    // return view('content/usermanagement/user-list', compact('user'));

    $finalDataArr = [];
    $user = Auth::user();
    $userData = User::where('status', '!=', 'deleted')->get();;

    foreach ($userData as $data) {

        // if (count($hascampaign) > 0) {
        if (!empty($data)) {

            $dataArr = array();
            $dataArr['id'] = $data->id;
            $dataArr['name'] = $data->name;
            $dataArr['email'] = $data->email;
            $dataArr['device_id'] = $data->device_id;
            $dataArr['status'] = $data->status;
            $expiry_date = "";
            if ($data->expiry_date != "") {
              $expiry_date = date("m/d/Y",strtotime($data->expiry_date));
            } 
            $dataArr['expiry_date'] = $expiry_date;
            $dataArr['created_at'] =  date("m/d/Y H:i:s",strtotime($data->created_at));
            
            $finalDataArr[] = $dataArr;
        }
    }

    // echo "<pre>"; print_r($finalDataArr); exit();
    return view('content/usermanagement/user-list', compact('finalDataArr', 'user'));


  }

  public function add_edit_user(){

    $user = Auth::user();
    $timezones = Timezones::all();
    return view('content/usermanagement/add-edit-user', compact('user','timezones'));

  }

  public function insert_update_user(Request $request){

    $error = $this->validator($request->all())->validate();

     // echo "<pre>"; print_r($error); exit();

    event(new Registered($user = $this->create($request->all())));
    // echo "<pre>"; print_r($user); exit();
    // $this->guard()->login($user);

    return redirect('usermanagement/user-list');
  }

  protected function validator(array $data)
  { 
    // echo "<pre>"; print_r($data); exit();
      return Validator::make($data, [
          'name' => ['required', 'string', 'max:255'],
          'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
          // 'timezone' => ['required', 'string'],
          'status' => ['required'],
          'device_id' => ['nullable','string'],
          'password' => ['required', 'string', 'min:6', 'confirmed'],
      ]);
  }

  protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'status' => $data['status'],
            'expiry_date' => $data['expiry_date'],
            'role' => $data['role'],
            'timezone' => $data['timezone'],
            'device_id' => $data['device_id'],
            'password' => Hash::make($data['password']),
        ]);

        // Convert device IDs to an array
        $deviceIds = explode(',', $data['device_id']);

        // Insert data into the attributes table
        $attributeValue = ['time' => 120];

        foreach ($deviceIds as $deviceId) {
            $deviceId = trim($deviceId); // Trim any extra whitespace
            
            // Prepare the attribute data
            $attributeData = [
                'user_id' => $user->id,
                'device_id' => $deviceId,
                'attributes' => json_encode($attributeValue), // Assuming 'attribute' in $data is an array or object
            ];
            
            // Check if the attribute record already exists
            $existingAttribute = Attributes::where('user_id', $user->id)->where('device_id', $deviceId)->first();
            
            if ($existingAttribute) {
                // Update the existing record
                $existingAttribute->update($attributeData);
            } else {
                // Create a new record
                Attributes::create($attributeData);
            }
        }

        return $user;
    }

    public function edit($id)
    {
        // Fetch the user by ID
        $userdata = User::find($id);
        $user = User::find($id);
        $timezones = Timezones::all();

        // Check if the user exists
        if (!$userdata) {
            abort(404, 'User not found');
        }

        // Pass the user to the view
        return view('content/usermanagement/add-edit-user', compact('userdata','timezones', 'user'));
    }

    public function dashboard($id){
      $userdata = User::find($id);
      $user = User::find($id);
      
      // Check if the user exists
      if (!$userdata) {
          abort(404, 'User not found');
      }
      return view('content/usermanagement/user-dashboard', compact('userdata', 'user'));
    }

    public function update_user_via_admin(Request $request){

      $post_data = $request->all();
       // echo "<pre>"; print_r($post_data); exit();
      $request->validate([
          'name' => ['required', 'string', 'max:255'],
          'email' => ['required', 'string', 'email', 'max:255'],
          'status' => ['required']
      ]);

      $name = $post_data['name'];
      $email = $post_data['email'];
      $status = $post_data['status'];
      $timezone = $post_data['timezone'];
      $expiry_date = $post_data['expiry_date'];
      $user_id = $post_data['user_id'];
      $device_id = $post_data['device_id'];
      $role = $post_data['role'];

      try {
      $user = User::find($user_id);
      $user->update([
            'name' => $name,
            'email' => $email,
            'status' => $status,
            'role' => $role,
            'expiry_date' => $expiry_date,
            'timezone' => $timezone,
            'device_id' => $device_id,
          ]);

        $deviceIds = explode(',', $device_id);
        
        // Insert data into the attributes table
        $attributeValue = ['time' => 120];

        foreach ($deviceIds as $deviceId) {
            $deviceId = trim($deviceId); // Trim any extra whitespace
            
            // Prepare the attribute data
            $attributeData = [
                'user_id' => $user_id,
                'device_id' => $deviceId,
                'attributes' => json_encode($attributeValue), // Assuming 'attribute' in $data is an array or object
            ];
            
            // Check if the attribute record already exists
            $existingAttribute = Attributes::where('user_id', $user_id)->where('device_id', $deviceId)->first();
            
            if ($existingAttribute) {
                // Update the existing record
                $existingAttribute->update($attributeData);
            } else {
                // Create a new record
                Attributes::create($attributeData);
            }
        }

      } catch (Exception $ex) {
          $message = $ex->getMessage();
      }
      return redirect('usermanagement/user-list');

    }

    public function delete_user_data(Request $request){
      $post_data = $request->all();
      // echo "<pre>"; print_r($post_data); exit();
      $user_id = $post_data['user_id'];
      try {
        $user = User::find($user_id);
        $user->delete();
        $message = "SUCCESS";
        $responseData = ['success' => 'success', 'error' => '', 'msg' => $message];
      } catch (Exception $ex) {
        $message = $ex->getMessage();
        $responseData = ['success' => 'failure', 'error' => '', 'msg' => $message];
      }

      return response()->json($responseData);

    }

}
