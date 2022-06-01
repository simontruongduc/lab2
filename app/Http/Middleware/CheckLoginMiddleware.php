<?php

namespace App\Http\Middleware;

use Closure;
use Flugg\Responder\Exceptions\Http\UnauthenticatedException;

class CheckLoginMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! $request->user('api')) {
            throw new UnauthenticatedException();
        } else {
            return $next($request);
        }
    }
}
