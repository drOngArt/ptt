<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guest()) {
            return $request->ajax()
                ? response('Unauthorized.', 401)
                : redirect()->guest('admin/login');
        }

        if (!Gate::allows('admin-only')) {
            return redirect('admin/login');
        }

        return $next($request);
    }
}