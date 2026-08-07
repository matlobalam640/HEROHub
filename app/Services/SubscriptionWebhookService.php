<?php

namespace App\Services;

use App\Mail\Membership\AdminMembershipEventMail;
use App\Mail\Membership\UserMembershipEventMail;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use App\Support\UsaPaymentsPlanMapper;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SubscriptionWebhookService
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array{membership: Membership, created: bool, user: ?User}
     */
    public function sync(array $payload): array
    {
        $subscriptionId = trim((string) ($payload['subscription_id'] ?? ''));
        if ($subscriptionId === '') {
            throw ValidationException::withMessages(['subscription_id' => 'subscription_id is required.']);
        }

        $planCode = UsaPaymentsPlanMapper::portalPlanCodeFromWebhookPayload($payload);
        if ($planCode === null || $planCode === '') {
            throw ValidationException::withMessages(['plan' => 'Could not resolve a plan identifier from payload.']);
        }

        $plan = Plan::query()->where('code', $planCode)->first();
        if (! $plan) {
            throw ValidationException::withMessages(['plan' => "No portal plan matches code {$planCode}."]);
        }

        $customer = $this->decodeJsonMaybe($payload['customer'] ?? null);
        $customer = is_array($customer) ? $customer : [];
        $email = $this->normalizeEmail(Arr::get($customer, 'email') ?? ($payload['email'] ?? null));
        if ($email === null) {
            throw ValidationException::withMessages(['customer.email' => 'Customer email is required to link a portal user.']);
        }

        $userCreated = false;
        $user = User::query()->where('email', $email)->first();
        if (! $user && config('heroportal.webhook_auto_create_users')) {
            $user = $this->createPortalUser($customer, $email);
            $userCreated = true;
        }
        if (! $user) {
            throw ValidationException::withMessages([
                'user' => 'No portal user exists for this email. Create the account first, or set HERO_WEBHOOK_AUTO_CREATE_USERS=true.',
            ]);
        }

        $membershipNumber = $this->resolveMembershipNumber($payload, $subscriptionId);
        $status = $this->mapStatus((string) ($payload['status'] ?? ''));
        [$coverageStart, $coverageEnd] = $this->resolveCoverageDates($payload);
        $billingTimeline = $this->resolveBillingTimeline($payload);
        $billingProvider = $this->resolveBillingProvider($payload);

        $result = DB::transaction(function () use (
            $payload,
            $plan,
            $user,
            $subscriptionId,
            $membershipNumber,
            $status,
            $coverageStart,
            $coverageEnd,
            $customer,
            $billingTimeline,
            $billingProvider
        ) {
            $customerId = (string) ($payload['customer_id'] ?? Arr::get($customer, 'customer_id') ?? '');

            $membership = Membership::query()->updateOrCreate(
                ['billing_subscription_id' => $subscriptionId],
                [
                    'membership_number' => $membershipNumber,
                    'plan_id' => $plan->id,
                    'account_user_id' => $user->id,
                    'coverage_starts_on' => $coverageStart,
                    'coverage_ends_on' => $coverageEnd,
                    'auto_renew' => $this->inferAutoRenew($payload),
                    'status' => $status,
                    'billing_provider' => $billingProvider,
                    'billing_customer_id' => $customerId !== '' ? $customerId : null,
                    'billing_subscription_created_at' => $billingTimeline['billing_subscription_created_at'],
                    'billing_next_billing_at' => $billingTimeline['billing_next_billing_at'],
                    'billing_last_billing_at' => $billingTimeline['billing_last_billing_at'],
                    'billing_auto_collect' => $billingTimeline['billing_auto_collect'],
                ]
            );

            $created = $membership->wasRecentlyCreated;
            $this->syncPrimaryMember($membership, $customer, $user);

            return ['membership' => $membership, 'created' => $created];
        });

        $membership = $result['membership']->fresh(['plan']);

        if (config('heroportal.webhook_new_membership_mail')) {
            $passwordResetUrl = $this->resolvePasswordResetUrlForNewUser($user, $userCreated);
            if ($result['created']) {
                $this->notifyMembershipCreated($membership, $user, $userCreated, $passwordResetUrl);
            } else {
                $this->notifyMembershipUpdated($membership, $user);
            }
        }

        return [
            'membership' => $membership,
            'created' => $result['created'],
            'user' => $user,
        ];
    }

    private function notifyMembershipCreated(Membership $membership, User $user, bool $userCreated, ?string $passwordResetUrl): void
    {
        $membershipUrl = route('customer.membership', [], true);
        $actionUrl = $membershipUrl;
        $actionLabel = 'Open My membership';
        $detailLines = [
            'Your membership has been activated in the HERO portal.',
            'Status: '.ucfirst((string) $membership->status),
        ];

        if ($userCreated && $passwordResetUrl) {
            $actionUrl = $passwordResetUrl;
            $actionLabel = 'Create your portal password';
            $detailLines[] = 'A portal account was created for this email from your subscription.';
            $detailLines[] = 'Use the button to set your password, then sign in to access your membership.';
        } elseif ($userCreated) {
            $detailLines[] = 'A portal account was created for this email. Use "Forgot password" on sign in if needed.';
        } else {
            $detailLines[] = 'Your existing portal account has been linked to this subscription.';
        }

        Mail::to($user->email)->queue(new UserMembershipEventMail(
            user: $user,
            subjectLine: 'Your HERO membership is active',
            headline: 'Your membership is now active in the portal.',
            membershipNumber: $membership->membership_number,
            planName: $membership->plan?->name,
            detailLines: $detailLines,
            actionUrl: $actionUrl,
            actionLabel: $actionLabel,
            footerNote: 'If you did not request this, contact HERO support immediately.',
        ));

        $this->notifyAdmins(
            subject: 'Admin alert: new membership created',
            headline: 'A new subscription created a portal membership.',
            detailLines: [
                'Membership #: '.$membership->membership_number,
                'Plan: '.($membership->plan?->name ?? '—'),
                'User email: '.$user->email,
                'Portal user newly created: '.($userCreated ? 'yes' : 'no'),
                'Billing subscription ID: '.($membership->billing_subscription_id ?: '—'),
            ],
            membership: $membership,
        );
    }

    private function notifyMembershipUpdated(Membership $membership, User $user): void
    {
        Mail::to($user->email)->queue(new UserMembershipEventMail(
            user: $user,
            subjectLine: 'Your HERO membership was updated',
            headline: 'Your membership details were updated from billing sync.',
            membershipNumber: $membership->membership_number,
            planName: $membership->plan?->name,
            detailLines: [
                'Status: '.ucfirst((string) $membership->status),
                'Coverage start: '.($membership->coverage_starts_on?->toDateString() ?? '—'),
                'Coverage end: '.($membership->coverage_ends_on?->toDateString() ?? '—'),
                'Next billing date: '.($membership->billing_next_billing_at?->toDateString() ?? '—'),
            ],
            actionUrl: route('customer.membership', [], true),
            actionLabel: 'Review membership',
            footerNote: 'If these details are unexpected, contact HERO support.',
        ));

        $this->notifyAdmins(
            subject: 'Admin alert: membership updated',
            headline: 'A subscription update modified an existing membership.',
            detailLines: [
                'Membership #: '.$membership->membership_number,
                'Plan: '.($membership->plan?->name ?? '—'),
                'User email: '.$user->email,
                'Status: '.ucfirst((string) $membership->status),
                'Billing subscription ID: '.($membership->billing_subscription_id ?: '—'),
            ],
            membership: $membership,
        );
    }

    private function notifyAdmins(string $subject, string $headline, array $detailLines, Membership $membership): void
    {
        $adminEmails = User::role('admin')
            ->whereNotNull('email')
            ->pluck('email')
            ->filter(fn ($email) => is_string($email) && $email !== '')
            ->unique()
            ->values()
            ->all();

        foreach ($adminEmails as $adminEmail) {
            Mail::to($adminEmail)->queue(new AdminMembershipEventMail(
                subjectLine: $subject,
                headline: $headline,
                detailLines: $detailLines,
                actionUrl: route('portal.membership.show', ['membership' => $membership->id], true),
                actionLabel: 'Open membership record',
            ));
        }
    }

    private function resolvePasswordResetUrlForNewUser(User $user, bool $userCreated): ?string
    {
        if (! $userCreated) {
            return null;
        }

        $passwordResetUrl = null;
        Password::broker()->sendResetLink(
            ['email' => $user->email],
            function (CanResetPassword $resetUser, string $token) use (&$passwordResetUrl): string {
                $passwordResetUrl = url(route('password.reset', [
                    'token' => $token,
                    'email' => $resetUser->getEmailForPasswordReset(),
                ], false));

                return Password::RESET_LINK_SENT;
            }
        );

        return $passwordResetUrl;
    }

    private function resolvePlanCode(array $payload): ?string
    {
        foreach (['plan_code', 'gateway_plan_code', 'membership_plan_code', 'plan_identifier', 'product_code', 'sku'] as $key) {
            $value = trim((string) ($payload[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        $lineItems = $this->decodeJsonMaybe($payload['line_items'] ?? null);
        if (is_array($lineItems) && isset($lineItems[0]) && is_array($lineItems[0])) {
            foreach (['code', 'plan_code', 'product_code', 'sku'] as $key) {
                $value = trim((string) ($lineItems[0][$key] ?? ''));
                if ($value !== '') {
                    return $value;
                }
            }
        }

        $plan = $this->decodeJsonMaybe($payload['plan'] ?? null);
        if (is_array($plan)) {
            foreach (['plan_code', 'code', 'product_code', 'sku'] as $key) {
                $value = trim((string) ($plan[$key] ?? ''));
                if ($value !== '') {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    private function resolveCoverageDates(array $payload): array
    {
        $start = $this->parseDate((string) ($payload['coverage_starts_on'] ?? $payload['start_date'] ?? $payload['activated_at'] ?? $payload['membership_start_date'] ?? ''));
        $end = $this->parseDate((string) ($payload['coverage_ends_on'] ?? $payload['current_term_ends_at'] ?? $payload['expires_at'] ?? $payload['membership_end_date'] ?? ''));

        return [$start, $end];
    }

    private function parseDate(string $value): ?Carbon
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array{
     *     billing_subscription_created_at: ?Carbon,
     *     billing_next_billing_at: ?Carbon,
     *     billing_last_billing_at: ?Carbon,
     *     billing_auto_collect: ?bool
     * }
     */
    private function resolveBillingTimeline(array $payload): array
    {
        $createdAt = null;
        $createdTime = trim((string) ($payload['created_time'] ?? ''));
        if ($createdTime !== '') {
            try {
                $createdAt = Carbon::parse($createdTime);
            } catch (\Throwable) {
                $createdAt = null;
            }
        }
        if ($createdAt === null) {
            $createdAt = $this->parseDate((string) ($payload['created_at'] ?? $payload['created_date'] ?? ''));
        }

        $next = $this->parseDate((string) ($payload['next_billing_at'] ?? ''));
        $last = $this->parseDate((string) ($payload['last_billing_at'] ?? ''));
        $autoCollect = $this->parseBool($payload['auto_collect'] ?? null);

        return [
            'billing_subscription_created_at' => $createdAt,
            'billing_next_billing_at' => $next,
            'billing_last_billing_at' => $last,
            'billing_auto_collect' => $autoCollect,
        ];
    }

    private function parseBool(mixed $value): ?bool
    {
        if ($value === null) {
            return null;
        }
        if (is_bool($value)) {
            return $value;
        }
        $s = strtolower(trim((string) $value));

        return match ($s) {
            '1', 'true', 'yes', 'on' => true,
            '0', 'false', 'no', 'off' => false,
            default => null,
        };
    }

    private function mapStatus(string $status): string
    {
        return match (strtolower(trim($status))) {
            'live', 'active', 'paid', 'success', 'succeeded', 'completed' => 'active',
            'cancelled', 'canceled' => 'cancelled',
            'expired' => 'expired',
            'paused', 'unpaid', 'past_due', 'inactive' => 'inactive',
            default => 'inactive',
        };
    }

    private function inferAutoRenew(array $payload): bool
    {
        $scheduledCancel = trim((string) ($payload['scheduled_cancellation_date'] ?? ''));
        if ($scheduledCancel !== '') {
            return false;
        }

        $status = strtolower((string) ($payload['status'] ?? ''));
        if ($status === 'cancelled' || $status === 'canceled' || $status === 'expired') {
            return false;
        }

        return true;
    }

    private function resolveBillingProvider(array $payload): string
    {
        $raw = strtolower(trim((string) ($payload['billing_provider'] ?? $payload['provider'] ?? $payload['source'] ?? 'manual')));

        return match ($raw) {
            'stripe' => 'stripe',
            'usa_payments', 'usa payments', 'usapayments' => 'usa_payments',
            'manual' => 'manual',
            default => 'manual',
        };
    }

    private function resolveMembershipNumber(array $payload, string $subscriptionId): string
    {
        $existing = Membership::query()->where('billing_subscription_id', $subscriptionId)->value('membership_number');
        if (is_string($existing) && $existing !== '') {
            return strtoupper($existing);
        }

        $direct = strtoupper(trim((string) ($payload['membership_number'] ?? '')));
        if ($direct !== '' && ! Membership::query()->where('membership_number', $direct)->exists()) {
            return $direct;
        }

        $subNo = trim((string) ($payload['subscription_number'] ?? ''));
        if ($subNo !== '') {
            $candidate = str_starts_with(strtoupper($subNo), 'SUB-')
                ? 'HERO-'.strtoupper($subNo)
                : 'HERO-SUB-'.strtoupper($subNo);
            if (! Membership::query()->where('membership_number', $candidate)->exists()) {
                return $candidate;
            }
        }

        return 'HERO-SUB-'.strtoupper(substr(sha1($subscriptionId), 0, 12));
    }

    /**
     * @param  array<string, mixed>  $customer
     */
    private function syncPrimaryMember(Membership $membership, array $customer, User $user): void
    {
        $display = trim((string) (Arr::get($customer, 'display_name') ?: $user->name));
        $parts = preg_split('/\s+/', $display, 2, PREG_SPLIT_NO_EMPTY) ?: [];
        $first = $parts[0] ?? 'Member';
        $last = $parts[1] ?? 'Member';

        $primary = Member::query()->firstOrNew([
            'membership_id' => $membership->id,
            'is_primary' => true,
        ]);

        $primary->fill([
            'first_name' => $first,
            'last_name' => $last,
            'email' => $user->email,
            'phone' => trim((string) (Arr::get($customer, 'phone') ?? '')) ?: null,
            'country' => trim((string) (Arr::get($customer, 'country') ?? '')) ?: $primary->country,
            'city' => trim((string) (Arr::get($customer, 'city') ?? '')) ?: $primary->city,
        ]);

        if (! $primary->qr_token) {
            $primary->qr_token = (string) Str::uuid();
        }
        $primary->save();
    }

    /**
     * @param  array<string, mixed>  $customer
     */
    private function createPortalUser(array $customer, string $email): User
    {
        $display = trim((string) (Arr::get($customer, 'display_name') ?: $email));
        $name = $display !== '' ? $display : $email;

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Str::password(32),
            'email_verified_at' => now(),
        ]);
        $user->assignRole('customer');

        return $user;
    }

    private function normalizeEmail(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }
        $email = strtolower(trim($value));

        return $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    private function decodeJsonMaybe(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }
        $trim = trim($value);
        if ($trim === '' || ($trim[0] !== '{' && $trim[0] !== '[')) {
            return $value;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }
}
