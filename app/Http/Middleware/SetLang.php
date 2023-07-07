<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\App;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLang
{
    private function getMainLang(): string
    {
        return 'ru';
    }

    private function getAllLangs(): array
    {
        return ['ru', 'uz', 'en'];
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $lang = in_array($request->header('lang'), $this->getAllLangs()) ? $request->header('lang') : $this->getMainLang();
        App::setLocale($lang);

        return $next($request);
    }
}
