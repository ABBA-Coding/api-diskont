<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Check1c
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
        $data = [
            'username' => 'for1c',
            'password' => 'wxaEy&696Zpl'
        ];
        $str = substr($request->headers->get('authorization'), 6);
        $credentials = base64_decode($str);
        $username = explode( ':', $credentials)[0];
        $password = explode( ':', $credentials)[1];
        if($username === $data['username'] && $password === $data['password']) {
            return $next($request);
        }

        return response([
            'message' => 'Unauthorized'
        ], 401);
    }
}
