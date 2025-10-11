<?php namespace App\Http\Controllers\Wall;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Role;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class LoginController extends Controller {

	public function showLogin(){
		return view('wall.login');
	}

	public function postLogin(){
		$userdata = array(
			'username' => Input::get('username'),
			'password' => Input::get('password') 
		);
        if (Auth::attempt($userdata, true)) {
            if(Auth::user()->hasRole('wall'))
                return redirect('/wall');
        }
        return redirect('/wall/login')
            ->withErrors(array('message' => 'Brak uprawnień'))
            ->withInput(Input::except('password'));
	}
}
