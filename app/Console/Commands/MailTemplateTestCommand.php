<?php

namespace App\Console\Commands;

use App\Mail\Membership\AdminMembershipEventMail;
use App\Mail\Membership\UserMembershipEventMail;
use App\Mail\NewSubscriptionMembershipMail;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use stdClass;
use Throwable;

class MailTemplateTestCommand extends Command
{
    protected $signature = 'mail:template-test
                            {template=all : all|new-membership|user-event|admin-event|renewal-reminder}
                            {email? : Recipient address (defaults to MAIL_FROM_ADDRESS)}
                            {--no-db : Use synthetic sample data and skip DB reads}
                            {--force : Allow sending when APP_ENV is production}';

    protected $description = 'Send real branded email template tests.';

    public function handle(): int
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Refused in production. Use --force if you really intend to send.');

            return self::FAILURE;
        }

        $to = $this->argument('email') ?: (string) config('mail.from.address');
        if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid or missing email. Pass {email} or set MAIL_FROM_ADDRESS in .env.');

            return self::FAILURE;
        }

        $template = strtolower((string) $this->argument('template'));
        $sendAll = $template === 'all';
        $validTemplates = ['new-membership', 'user-event', 'admin-event', 'renewal-reminder'];
        if (! $sendAll && ! in_array($template, $validTemplates, true)) {
            $this->error('Unknown template. Use all|new-membership|user-event|admin-event|renewal-reminder.');

            return self::FAILURE;
        }

        [$user, $membership, $planName, $usingSynthetic] = $this->resolveTemplateContext();
        if (! $user || ! $membership) {
            $this->error('Could not build template context.');

            return self::FAILURE;
        }

        if ($sendAll || $template === 'new-membership') {
            if ($usingSynthetic) {
                Mail::send('mail.subscription.new-membership', [
                    'user' => $user,
                    'membership' => $membership,
                    'needsPasswordSetup' => false,
                    'passwordResetUrl' => null,
                    'planName' => $planName,
                ], function ($message) use ($to): void {
                    $message->to($to)->subject('Template test: new membership');
                });
            } else {
                Mail::to($to)->send(new NewSubscriptionMembershipMail(
                    user: $user,
                    membership: $membership,
                    needsPasswordSetup: false,
                    passwordResetUrl: null,
                    planName: $planName,
                ));
            }
            $this->info('Sent: new-membership');
        }

        if ($sendAll || $template === 'user-event') {
            $payload = [
                'user' => $user,
                'subject' => 'Template test: user membership event',
                'headline' => 'Your membership details were updated.',
                'membershipNumber' => $membership->membership_number,
                'planName' => $planName,
                'detailLines' => [
                    'Status: '.ucfirst((string) $membership->status),
                    'Coverage start: '.$this->toDateStringOrDash($membership->coverage_starts_on),
                    'Coverage end: '.$this->toDateStringOrDash($membership->coverage_ends_on),
                ],
                'actionUrl' => route('customer.membership', [], true),
                'actionLabel' => 'Review membership',
                'footerNote' => 'Template test message.',
            ];
            if ($usingSynthetic) {
                Mail::send('mail.membership.user-event', $payload, function ($message) use ($to): void {
                    $message->to($to)->subject('Template test: user membership event');
                });
            } else {
                Mail::to($to)->send(new UserMembershipEventMail(
                    user: $user,
                    subjectLine: $payload['subject'],
                    headline: $payload['headline'],
                    membershipNumber: $payload['membershipNumber'],
                    planName: $payload['planName'],
                    detailLines: $payload['detailLines'],
                    actionUrl: $payload['actionUrl'],
                    actionLabel: $payload['actionLabel'],
                    footerNote: $payload['footerNote'],
                ));
            }
            $this->info('Sent: user-event');
        }

        if ($sendAll || $template === 'admin-event') {
            $payload = [
                'subject' => 'Template test: admin membership event',
                'headline' => 'A membership event requires admin review.',
                'detailLines' => [
                    'Membership #: '.$membership->membership_number,
                    'Plan: '.($planName ?: '—'),
                    'User: '.$user->email,
                    'Status: '.ucfirst((string) $membership->status),
                ],
                'actionUrl' => route('dashboard', [], true),
                'actionLabel' => 'Open dashboard',
            ];
            if ($usingSynthetic) {
                Mail::send('mail.membership.admin-event', $payload, function ($message) use ($to): void {
                    $message->to($to)->subject('Template test: admin membership event');
                });
            } else {
                Mail::to($to)->send(new AdminMembershipEventMail(
                    subjectLine: $payload['subject'],
                    headline: $payload['headline'],
                    detailLines: $payload['detailLines'],
                    actionUrl: route('portal.membership.show', ['membership' => $membership->id], true),
                    actionLabel: 'Open membership record',
                ));
            }
            $this->info('Sent: admin-event');
        }

        if ($sendAll || $template === 'renewal-reminder') {
            Mail::send('mail.membership.renewal-reminder', [
                'subject' => 'Template test: renewal reminder',
                'daysUntilRenewal' => 10,
                'membershipNumber' => $membership->membership_number,
                'planName' => $planName,
                'renewalDate' => now()->addDays(10)->toDateString(),
                'actionUrl' => route('customer.membership', [], true),
                'actionLabel' => 'Review membership',
                'footerNote' => 'Template test message.',
            ], function ($message) use ($to): void {
                $message->to($to)->subject('Template test: renewal reminder');
            });
            $this->info('Sent: renewal-reminder');
        }

        return self::SUCCESS;
    }

    /**
     * @return array{0: User|stdClass, 1: Membership|stdClass, 2: ?string, 3: bool}
     */
    private function resolveTemplateContext(): array
    {
        if ($this->option('no-db')) {
            $this->warn('Using synthetic sample data (--no-db).');

            return $this->syntheticContext();
        }

        try {
            $membership = Membership::query()->with('plan')->first();
            if (! $membership) {
                $this->warn('No membership found in DB. Falling back to synthetic sample data.');

                return $this->syntheticContext();
            }

            $user = $membership->accountUser ?: User::query()->first();
            if (! $user) {
                $this->warn('No user found in DB. Falling back to synthetic sample data.');

                return $this->syntheticContext();
            }

            return [$user, $membership, $membership->plan?->name, false];
        } catch (Throwable $e) {
            $this->warn('DB unavailable ('.$e->getMessage().'). Falling back to synthetic sample data.');

            return $this->syntheticContext();
        }
    }

    /**
     * @return array{0: stdClass, 1: stdClass, 2: string, 3: bool}
     */
    private function syntheticContext(): array
    {
        $user = new stdClass;
        $user->name = 'Template Test User';
        $user->email = 'template-test@example.com';

        $membership = new stdClass;
        $membership->membership_number = 'HERO-TPL-TEST-001';
        $membership->status = 'active';
        $membership->coverage_starts_on = now()->toDateString();
        $membership->coverage_ends_on = now()->addYear()->toDateString();
        $membership->id = null;

        return [$user, $membership, 'Template Demo Plan', true];
    }

    private function toDateStringOrDash(mixed $value): string
    {
        if (is_object($value) && method_exists($value, 'toDateString')) {
            return (string) $value->toDateString();
        }
        if (is_string($value) && trim($value) !== '') {
            return $value;
        }

        return '—';
    }
}
