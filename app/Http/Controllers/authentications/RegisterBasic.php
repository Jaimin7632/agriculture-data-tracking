<?php

namespace App\Http\Controllers\authentications;

use App\Models\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;

class RegisterBasic extends Controller
{

	use RegistersUsers;

	protected $redirectTo = '/';
    //protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {	
        $this->middleware('guest');
    }

    public function register(Request $request)
    {	

        $this->validator($request->all())->validate();

        // echo "<pre>"; print_r($request->all()); exit();

        event(new Registered($user = $this->create($request->all())));
        // echo "<pre>"; print_r($user); exit();
        // $this->guard()->login($user);

        return redirect("auth/login-basic");
        /*return $this->registered($request, $user)
                        ?: redirect($this->redirectPath());*/
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => Hash::make($data['password']),
        ]);
    }

  public function index()
  {
    return view('content.authentications.auth-register-basic');
  }
}
