<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * True when the user should use the customer self-service portal (no staff dashboard).
     */
    public static function isCustomerPortalOnly(?Authenticatable $user): bool
    {
        if (! $user || ! method_exists($user, 'hasRole')) {
            return false;
        }

        return $user->hasRole('customer')
            && ! $user->hasAnyRole(['admin', 'dispatch', 'partner', 'business']);
    }

    /**
     * HR / company portal home (business role, not admin).
     */
    public static function isBusinessPortalPrimary(?Authenticatable $user): bool
    {
        if (! $user || ! method_exists($user, 'hasRole')) {
            return false;
        }

        return $user->hasRole('business') && ! $user->hasRole('admin');
    }

    /**
     * Partner / reseller portal (partner role, not admin).
     */
    public static function isPartnerPortalPrimary(?Authenticatable $user): bool
    {
        if (! $user || ! method_exists($user, 'hasRole')) {
            return false;
        }

        return $user->hasRole('partner') && ! $user->hasRole('admin');
    }

    /**
     * Post-login / "home" URL: customers → membership; business HR → company portal; else dashboard.
     */
    public static function homeUrlFor(?Authenticatable $user): string
    {
        if (static::isCustomerPortalOnly($user)) {
            return route('customer.membership', [], false);
        }

        if (static::isBusinessPortalPrimary($user)) {
            return route('business.portal', [], false);
        }

        if (static::isPartnerPortalPrimary($user)) {
            return route('partner.portal', [], false);
        }

        return static::HOME;
    }

    /**
     * Home URL with email verification query flag (Breeze-style).
     */
    public static function verifiedHomeUrlFor(?Authenticatable $user): string
    {
        $base = static::homeUrlFor($user);

        return $base.(str_contains($base, '?') ? '&' : '?').'verified=1';
    }

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
