<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class LoginController extends Controller
{
    public function showLogin()
    {
        return view('admin.login');
    }

    public function postLogin(Request $request)
    {
        $userdata = [
            'username' => $request->input('username'),
            'password' => $request->input('password'),
        ];

        if (Auth::attempt($userdata, true)) {
            if (Gate::allows('admin-only')) {
                if (Cache::has('tournamentDirectory')) {
                    return redirect('admin');
                } else {
                    return redirect('admin/chooseTournament');
                }
            } else {
                return redirect('admin/login')
                    ->withErrors(['message' => 'Brak uprawnień'])
                    ->withInput($request->except('password'));
            }
        }

        return redirect('admin/login')
            ->withErrors(['message' => 'Nieprawidłowy użytkownik lub hasło'])
            ->withInput($request->except('password'));
    }
}