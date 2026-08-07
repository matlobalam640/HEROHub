<?php

namespace App\Http\Middleware;

use App\Support\CoverageFormTranslations;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPortalLocale
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('portal_locale', config('app.locale', 'en'));

        if (in_array($locale, CoverageFormTranslations::supportedLocales(), true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
