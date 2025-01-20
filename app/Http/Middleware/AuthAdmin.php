<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;
use Session;
use Symfony\Component\HttpFoundation\Response;

class AuthAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if(Auth::check())
        {
            if(Auth::user()->role_id === 1)
            {
                return $next($request);
            }
            else
            {
                Auth::logout();
                return redirect()->route('admin.login');
            }
        }
        else{
            return redirect()->route('admin.login');
        }
    }
}
