<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
//        if(auth()->user()->tokenCan('admin') && auth()->user()->role_id == null) return $next($request);

//        dd($request->method());
        return $next($request);
    }

    public function get_url($request)
    {
        $url = $request->getRequestUri();
        $url = substr($url, 11);

        return $url;
    }
}
