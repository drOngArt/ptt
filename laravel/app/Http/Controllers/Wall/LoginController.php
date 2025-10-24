<?php

namespace App\Http\Controllers\Wall;

use App\Http\Controllers\Controller;
// use App\Role;
// use Auth;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Input;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Input;

class LoginController extends Controller
{
    public function showLogin()
    {
        return view('wall.login');
    }

    public function postLogin()
    {
        $userdata = [
            'username' => Input::get('username'),
            'password' => Input::get('password'),
        ];
        if (Auth::attempt($userdata, true)) {
            if (Gate::allows('wall-only')) {
                return redirect('/wall');
            }
        }

        return redirect('/wall/login')
            ->withErrors(['message' => 'Brak uprawnień'])
            ->withInput(Input::except('password'));
    }
}
