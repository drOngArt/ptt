<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Gate;

class AdminMiddleware {

    protected $auth;

    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

	public function handle($request, Closure $next)
	{
        if ($this->auth->guest())
        {
            if ($request->ajax())
            {
                return response('Unauthorized.', 401);
            }
            else
            {
                return redirect()->guest('admin/login');
            }
        }
        if (Gate::allows('admin-only'))
            return $next($request);
                
        return redirect()->guest('admin/login');
	}

}
