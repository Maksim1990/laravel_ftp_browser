<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Auth;
use Illuminate\Support\Facades\Session;

class CheckLoggedIn
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (Auth::check()) {
            //-- TODO Add check logging logic here
            return $next($request);
        }

        return redirect('/login');

    }
}