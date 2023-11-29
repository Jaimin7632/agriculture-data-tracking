<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Models\User;

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
    $userData = User::all();
    echo "<pre>"; print_r($userData); exit();
    return view('content.authentications.auth-login-basic');
  }

  protected function authenticated(Request $request, $user) {
        // echo '<pre>'; print_r($user->role) ; exit();
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
