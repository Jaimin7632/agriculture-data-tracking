<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginBasic extends Controller
{

	use AuthenticatesUsers;

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
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

  public function index()
  {
    return view('content.authentications.auth-login-basic');
  }

  protected function authenticated(Request $request, $user) {
           
         echo $user->expiry_date; 
         echo date("Y-m-d");
        if ($user->expiry_date == "") {
            $expiry_date = date("Y-m-d");
        }else{
            $expiry_date = $user->expiry_date;
        }
        if ($user->status == 'inactive' || $expiry_date < date("Y-m-d")) {
            \Auth::logout();
            return redirect(url('/login'))->with('message', 'YOUR SUBSCRIPTION EXPIRED PLEASE CONTACT ADMIN.');
        }

        if ($user->role == "admin") {
            return redirect('/');
        } else {
            return redirect('/');
        }
    }

    public function logout() {
        \Auth::logout();
        return redirect(url('/login'));
    }

    public function webpage(Request $request){
        echo "hello"; exit();
    }

}
