<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Session\TokenMismatchException;

class ExcludeCsrfTokenMiddleware extends VerifyCsrfToken {

    public function handle($request, Closure $next)
    {
        if ($this->isReading($request) || $this->excludedRoutes($request) || $this->tokensMatch($request))
        {
            return $this->addCookieToResponse($request, $next($request));
        }

        throw new TokenMismatchException;
    }

    protected function excludedRoutes($request)
    {
        $routes = [
            'judge/round',
            'judge/round/isNewRound',
            'api/v1/login'
        ];

        foreach($routes as $route)
            if ($request->is($route))
                return true;

        return false;
    }
}

