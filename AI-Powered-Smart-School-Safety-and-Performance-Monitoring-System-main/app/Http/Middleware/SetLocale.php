<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Get locale from database settings
            $setting = Setting::first();
            $locale = $setting ? $setting->language : config('app.locale', 'en');

            // Fallback to session locale if database is not available
            if (!$locale) {
                $locale = session('locale', config('app.locale', 'en'));
            }

            // Validate locale
            $supportedLocales = ['en', 'si'];
            if (!in_array($locale, $supportedLocales)) {
                $locale = 'en';
            }

            // Set application locale
            app()->setLocale($locale);

            // Store in session for consistency
            session(['locale' => $locale]);
        } catch (\Exception $e) {
            // If database is not available, use default locale
            app()->setLocale(config('app.locale', 'en'));
        }

        return $next($request);
    }
}
