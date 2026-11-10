<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('lang')) {
            $requested = strtolower((string) $request->query('lang'));
            if (in_array($requested, ['id', 'en'], true)) {
                $request->session()->put('locale', $requested);
            }
        }

        $locale = (string) $request->session()->get('locale', 'id');
        if (! in_array($locale, ['id', 'en'], true)) {
            $locale = 'id';
        }

        App::setLocale($locale);

        return $next($request);
    }
}

