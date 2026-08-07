<?php

namespace App\Providers;

use App\Mail\Transport\PhpMailTransport;
use App\Support\CoverageProfileRequirement;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Mail::extend('phpmail', function (array $config) {
            return new PhpMailTransport(
                (string) ($config['params'] ?? '')
            );
        });

        View::composer('layouts.portal', function ($view): void {
            $user = auth()->user();
            if (! $user || ! CoverageProfileRequirement::shouldPromptUser($user)) {
                $view->with([
                    'coverageProfileIncomplete' => false,
                    'coverageProfileMissingLabels' => [],
                ]);

                return;
            }

            $membership = CoverageProfileRequirement::membershipForUser($user);
            $primary = $membership ? CoverageProfileRequirement::primaryMember($membership) : null;

            $view->with([
                'coverageProfileIncomplete' => true,
                'coverageProfileMissingLabels' => CoverageProfileRequirement::missingFieldLabels($membership, $primary),
            ]);
        });
    }
}
