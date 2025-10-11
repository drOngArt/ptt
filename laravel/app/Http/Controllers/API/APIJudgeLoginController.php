<?php namespace App\Http\Controllers\API;

use App;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Response;

class APIJudgeLoginController extends Controller {

    public function postLogin(){
        /*$credentials = [
            'username' => Input::get('username'),
            'password' => Input::get('password'),
        ];
        if(Auth::once($credentials)){
            return Response::json();
        }
        else{
            return Response::json(array(),401);
        }*/
        return Response::json([],200);
    }

}
