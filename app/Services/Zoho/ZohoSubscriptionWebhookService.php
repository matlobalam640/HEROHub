<?php

namespace App\Services\Zoho;

use App\Mail\NewSubscriptionMembershipMail;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ZohoSubscriptionWebhookService
{
    /**
     * @param  array<string, mixed>  $payload  Flat Zoho subscription object (nested JSON may be strings).
     * @return array{membership: Membership, created: bool, user: ?User}
     */
    public function sync(array $payload): array
    {
        $subscriptionId = (string) ($payload['subscription_id'] ?? '');
        if ($subscriptionId === '') {
            throw ValidationException::withMessages(['subscription_id' => 'subscription_id is required.']);
        }

        $planCode = $this->resolvePlanCode($payload);
        if ($planCode === null || $planCode === '') {
            throw ValidationException::withMessages(['plan' => 'Could not resolve Zoho plan code from line_items or plan.']);
        }

        $plan = Plan::query()
            ->where(function ($q) use ($planCode) {
                $q->where('zoho_code_monthly', $planCode)
                    ->orWhere('zoho_code_yearly', $planCode);
            })
            ->first();

        if (! $plan) {
            throw ValidationException::withMessages(['plan' => "No portal plan matches Zoho code {$planCode} (check zoho_code_monthly / zoho_code_yearly)."]);
        }

        $customer = $this->decodeJsonMaybe($payload['customer'] ?? null);
        $customer = is_array($customer) ? $customer : [];

        $email = $this->normalizeEmail(Arr::get($customer, 'email'));
        if ($email === null) {
            $email = $this->emailFromContactPersons($payload);
        }
        if ($email === null) {
            throw ValidationException::withMessages(['customer.email' => 'Customer email is required to link a portal user.']);
        }

        $userCreated = false;
        $user = User::query()->where('email', $email)->first();
        if (! $user && config('heroportal.zoho_webhook_auto_create_users')) {
            $user = $this->createPortalUser($customer, $email);
            $userCreated = true;
        }
        if (! $user) {
            throw ValidationException::withMessages([
                'user' => 'No portal user exists for this email. Create the account first, or set ZOHO_WEBHOOK_AUTO_CREATE_USERS=true.',
            ]);
        }

        $membershipNumber = $this->resolveMembershipNumber($payload, $subscriptionId);

        $status = $this->mapStatus((string) ($payload['status'] ?? ''));
        [$coverageStart, $coverageEnd] = $this->resolveCoverageDates($payload);

        $result = DB::transaction(function () use (
            $payload,
            $plan,
            $user,
            $subscriptionId,
            $membershipNumber,
            $status,
            $coverageStart,
            $coverageEnd,
            $customer
        ) {
            $customerId = (string) ($payload['customer_id'] ?? Arr::get($customer, 'customer_id') ?? '');
            $billingTimeline = $this->resolveBillingTimeline($payload);

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
                    'billing_provider' => 'zoho',
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

        if ($result['created'] && config('heroportal.zoho_webhook_new_membership_mail')) {
            $passwordResetUrl = null;
            if ($userCreated) {
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
            }

            Mail::to($user->email)->queue(new NewSubscriptionMembershipMail(
                $user,
                $membership,
                $userCreated,
                $passwordResetUrl,
                $membership->plan?->name,
            ));
        }

        return [
            'membership' => $membership,
            'created' => $result['created'],
            'user' => $user,
        ];
    }

    private function resolvePlanCode(array $payload): ?string
    {
        $lineItems = $this->decodeJsonMaybe($payload['line_items'] ?? null);
        if (is_array($lineItems) && isset($lineItems[0]['code'])) {
            return (string) $lineItems[0]['code'];
        }

        $plan = $this->decodeJsonMaybe($payload['plan'] ?? null);
        if (is_array($plan) && isset($plan['plan_code'])) {
            return (string) $plan['plan_code'];
        }

        return null;
    }

    /**
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    private function resolveCoverageDates(array $payload): array
    {
        $start = $this->parseZohoDate((string) ($payload['start_date'] ?? $payload['activated_at'] ?? ''));
        $end = $this->parseZohoDate((string) ($payload['current_term_ends_at'] ?? $payload['expires_at'] ?? ''));

        return [$start, $end];
    }

    private function parseZohoDate(string $value): ?Carbon
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
            $createdAt = $this->parseZohoDate((string) ($payload['created_at'] ?? $payload['created_date'] ?? ''));
        }

        $next = $this->parseZohoDate((string) ($payload['next_billing_at'] ?? ''));
        $last = $this->parseZohoDate((string) ($payload['last_billing_at'] ?? ''));

        $autoCollect = $this->parseZohoBool($payload['auto_collect'] ?? null);

        return [
            'billing_subscription_created_at' => $createdAt,
            'billing_next_billing_at' => $next,
            'billing_last_billing_at' => $last,
            'billing_auto_collect' => $autoCollect,
        ];
    }

    private function parseZohoBool(mixed $value): ?bool
    {
        if ($value === null) {
            return null;
        }
        if (is_bool($value)) {
            return $value;
        }
        $s = strtolower(trim((string) $value));
        if ($s === '') {
            return null;
        }

        return match ($s) {
            '1', 'true', 'yes', 'on' => true,
            '0', 'false', 'no', 'off' => false,
            default => null,
        };
    }

    private function mapStatus(string $status): string
    {
        return match (strtolower($status)) {
            'live', 'active' => 'active',
            'cancelled', 'canceled' => 'cancelled',
            'expired' => 'expired',
            'paused', 'unpaid', 'past_due' => 'inactive',
            default => 'inactive',
        };
    }

    private function inferAutoRenew(array $payload): bool
    {
        $scd = $payload['scheduled_cancellation_date'] ?? '';
        if (is_string($scd) && trim($scd) !== '') {
            return false;
        }

        return strtolower((string) ($payload['status'] ?? '')) === 'live';
    }

    private function resolveMembershipNumber(array $payload, string $subscriptionId): string
    {
        $existing = Membership::query()->where('billing_subscription_id', $subscriptionId)->value('membership_number');
        if (is_string($existing) && $existing !== '') {
            return $existing;
        }

        $subNo = trim((string) ($payload['subscription_number'] ?? ''));
        if ($subNo !== '') {
            $candidate = 'ZOHO-'.$subNo;
            if (! Membership::query()->where('membership_number', $candidate)->exists()) {
                return $candidate;
            }
        }

        return 'ZOHO-SUB-'.substr(sha1($subscriptionId), 0, 12);
    }

    /**
     * @param  array<string, mixed>  $customer
     */
    private function syncPrimaryMember(Membership $membership, array $customer, User $user): void
    {
        $display = trim((string) (Arr::get($customer, 'display_name') ?: $user->name));
        $parts = preg_split('/\s+/', $display, 2, PREG_SPLIT_NO_EMPTY) ?: [];
        $first = $parts[0] ?? 'Member';
        $last = $parts[1] ?? '';

        $primary = Member::query()->firstOrNew([
            'membership_id' => $membership->id,
            'is_primary' => true,
        ]);

        $primary->fill([
            'first_name' => $first,
            'last_name' => $last !== '' ? $last : 'Member',
            'email' => $user->email,
            'phone' => trim((string) (Arr::get($customer, 'phone') ?? '')) ?: null,
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

    private function emailFromContactPersons(array $payload): ?string
    {
        $raw = $this->decodeJsonMaybe($payload['contactpersons'] ?? $payload['contact_persons_associated'] ?? null);
        if (! is_array($raw) || $raw === []) {
            return null;
        }
        $first = $raw[0] ?? null;
        if (! is_array($first)) {
            return null;
        }

        return $this->normalizeEmail($first['email'] ?? $first['contact_person_email'] ?? null);
    }

    private function normalizeEmail(mixed $email): ?string
    {
        if (! is_string($email)) {
            return null;
        }
        $email = strtolower(trim($email));

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
