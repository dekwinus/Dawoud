<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Is_Active
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            \Log::info("Is_Active Middleware: User check for ID " . Auth::user()->id);
        } else {
             \Log::info("Is_Active Middleware: No User authenticated");
        }
        
        $response = $next($request);
        // If the status is not approved redirect to login
        if (Auth::check() && ! Auth::user()->statut) {
            Auth::logout();

            return redirect('/login')->with('erro_login', 'Your error text');
        }

        return $response;
    }
}
