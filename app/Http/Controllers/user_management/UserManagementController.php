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
    $userData = User::all();

    foreach ($userData as $data) {

        // if (count($hascampaign) > 0) {
        if (!empty($data)) {

            $dataArr = array();
            $dataArr['id'] = $data->id;
            $dataArr['name'] = $data->name;
            $dataArr['email'] = $data->email;
            $dataArr['created_at'] =  date("m/d/Y H:i:s",strtotime($data->created_at));
            
            $finalDataArr[] = $dataArr;
        }
    }

    // echo "<pre>"; print_r($finalDataArr); exit();
    return view('content/usermanagement/user-list', compact('finalDataArr', 'user'));


  }

  public function add_edit_user(){

    $user = Auth::user();
    return view('content/usermanagement/add-edit-user', compact('user'));

  }

  public function insert_update_user(Request $request){

    $error = $this->validator($request->all())->validate();

     // echo "<pre>"; print_r($error); exit();

    event(new Registered($user = $this->create($request->all())));
    // echo "<pre>"; print_r($user); exit();
    // $this->guard()->login($user);

    return redirect('/');
  }

  protected function validator(array $data)
  { 
    // echo "<pre>"; print_r($data); exit();
      return Validator::make($data, [
          'name' => ['required', 'string', 'max:255'],
          'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
          'password' => ['required', 'string', 'min:6', 'confirmed'],
      ]);
  }

  protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => Hash::make($data['password']),
        ]);
    }

  

}
