<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jenssegers\Mongodb\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;

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
       return view('content/dashboard/dashboards-analytics', compact('user'));
    }else{
       return view('content/dashboard/userdashboards-analytics', compact('user'));
    }

  }
}
