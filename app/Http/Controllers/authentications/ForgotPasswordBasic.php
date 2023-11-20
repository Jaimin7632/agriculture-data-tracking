<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordBasic extends Controller
{

	use SendsPasswordResetEmails;

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
	    $this->middleware('guest');
	}
    
	public function index()
	{
	return view('content.authentications.auth-forgot-password-basic');
	}
}
