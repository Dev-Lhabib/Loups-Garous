<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale', config('app.fallback_locale', 'en'));

        if (in_array($locale, ['en', 'fr', 'ar'])) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
