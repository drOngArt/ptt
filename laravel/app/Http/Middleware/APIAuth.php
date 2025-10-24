<?php

namespace App\Http\Middleware;

use Auth;
use Closure;

class APIAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return Auth::onceBasic('username') ?: $next($request);
    }
}
