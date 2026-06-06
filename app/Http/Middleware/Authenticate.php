<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        // If setup exists and is not completed, send web traffic there.
        if (! Storage::disk('public')->exists('installed')) {
            if (Route::has('setup')) {
                return route('setup');
            }

            if ($request->expectsJson()) {
                return null;
            }
        }

        // Handle Online Store routes
        if ($request->is('online_store') || $request->is('online_store/*')) {
            if ($request->expectsJson()) {
                return null;
            }

            // Redirect to the store login page
            return url('/online_store/login');
        }

        // Default for admin panel or web
        if (! $request->expectsJson()) {
            return route('login');
        }

        return null;
    }
}
