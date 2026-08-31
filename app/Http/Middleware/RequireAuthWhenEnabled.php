<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireAuthWhenEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('cotiz.auth.enabled') || Auth::check()) {
            return $next($request);
        }

        return redirect()->guest(route('login'));
    }
}
