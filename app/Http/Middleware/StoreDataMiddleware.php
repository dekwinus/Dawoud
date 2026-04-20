<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\StoreSetting;

class StoreDataMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Share store settings with all views
        $settings = StoreSetting::first();
        
        if ($settings) {
            view()->share('s', $settings);
        }
        
        return $next($request);
    }
}
