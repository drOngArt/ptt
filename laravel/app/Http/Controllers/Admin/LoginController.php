<?php namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Gate;

//use Auth;
//use Cache;
//use Input;

class LoginController extends Controller {

	public function showLogin(){
		return view('admin.login');
	}

    public function postLogin(){
        $userdata = [
            'username' => Input::get('username'),
            'password' => Input::get('password')
        ];
        if (Auth::attempt($userdata, true)) {
            if (Gate::allows('admin-only')) {
                if(Cache::has('tournamentDirectory'))
                    return redirect('admin');
                else{
                    return redirect('admin/chooseTournament');
                }
            }
            else{
                return redirect('admin/login')
                    ->withErrors(['message' => 'Brak uprawnień'])
                    ->withInput(Input::except('password'));
            }
        }
        return redirect('admin/login')
            ->withErrors(['message' => 'Nieprawidłowy użytkownik lub hasło'])
            ->withInput(Input::except('password'));
    }

}
