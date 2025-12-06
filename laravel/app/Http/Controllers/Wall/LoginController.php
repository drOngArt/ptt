<?php

namespace App\Http\Controllers\Wall;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function showLogin()
    {
        return view('wall.login');
    }

    public function postLogin(Request $request)
    {
        $userdata = [
            'username' => $request->input('username'),
            'password' => $request->input('password'),
        ];
        if (Auth::attempt($userdata, true)) {
            if (Gate::allows('wall-only')) {
                return redirect('/wall');
            }
        }

        return redirect('/wall/login')
            ->withErrors(['message' => 'Brak uprawnień'])
            ->withInput($request->except('password'));
    }
}
