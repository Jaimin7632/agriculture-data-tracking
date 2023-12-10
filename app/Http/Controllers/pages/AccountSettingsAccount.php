<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AccountSettingsAccount extends Controller
{
  public function index()
  {
  	$user = Auth::user();
    return view('content.pages.pages-account-settings-account', compact('user'));
  }

  public function updateUserProfile(Request $request){
  	//echo "hello"; die();
  	// $error = $this->validator($request->all())->validate();
  	// event(new Registered($user = $this->create($request->all())));
  	// return redirect('/');

  	$post_data = $request->all();
    // echo "<pre>"; print_r($post_data); exit();
    $request->validate([
        'fullname' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255'],
        'country' => ['required', 'string'],
    ]);

    $fullname = $post_data['fullname'];
    $user_id = $post_data['user_id'];
    $email = $post_data['email'];
    $country = $post_data['country'];

    try {
	 	$user = User::find($user_id);
	 	$user->update([
        	'name' => $fullname,
        	'email' => $email,
        	'country' => $country,
        ]);
        $message = "SUCCESS";
        $responseData = ['success' => 'success', 'error' => '', 'msg' => $message];
    } catch (Exception $ex) {
        $message = $ex->getMessage();
        $responseData = ['success' => 'failure', 'error' => 'error', 'msg' => $message];
    }
    return response()->json($responseData);

  }

  public function updateUserPassword(Request $request){
  	$post_data = $request->all();
     // echo "<pre>"; print_r($post_data); exit();
    $request->validate([
        'old_password' => 'required',
        'password' => 'required|confirmed|min:6',
    ]);

    //echo "<pre>"; print_r($request->password_confirmation); exit();

    try {
	 	$user = auth()->user();

	 	if (!Hash::check($request->old_password, $user->password)) {
	        return redirect()->back()->withErrors(['old_password' => 'Incorrect old password']);
	    }

	 	$user->update([
	        'password' => Hash::make($request->password),
	    ]);
        $message = "SUCCESS";
        $responseData = ['success' => 'success', 'error' => '', 'msg' => $message];
    } catch (Exception $ex) {
        $message = $ex->getMessage();
        $responseData = ['success' => 'failure', 'error' => 'error', 'msg' => $message];
    }
    return response()->json($responseData);

  }

  protected function validator(array $data)
  { 
    // echo "<pre>"; print_r($data); exit();
      return Validator::make($data, [
          'name' => ['required', 'string', 'max:255'],
          'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
      ]);
  }

  protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);
    }


}
