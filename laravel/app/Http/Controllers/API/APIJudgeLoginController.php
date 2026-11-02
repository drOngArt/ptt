<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\JsonResponse;

class APIJudgeLoginController extends Controller
{
    public function postLogin(): JsonResponse
    {
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
        return response()->json([], 200);
    }
}
