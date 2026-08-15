<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BusinessMembershipProvisioner
{
    /** @var list<string> */
    public const RECORD_TYPES = ['b2c', 'b2b_company', 'b2b_employee'];

    public function __construct(
        private readonly CompanyBillingService $billingService = new CompanyBillingService(),
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function resolveRecordType(array $payload, Plan $plan): string
    {
        $explicit = strtolower(trim((string) ($payload['record_type'] ?? '')));
        if (in_array($explicit, self::RECORD_TYPES, true)) {
            return $explicit;
        }

        if ($plan->isBusinessCategory()) {
            return 'b2b_company';
        }

        return 'b2c';
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $customer
     * @return array{
     *     company_name: string,
     *     billing_email: ?string,
     *     phone: ?string,
     *     city: ?string,
     *     country: ?string,
     * }
     */
    public function companyContextFromPayload(array $payload, array $customer, string $ownerEmail): array
    {
        $companyName = trim((string) (
            $payload['company_name']
            ?? Arr::get($customer, 'company_name')
            ?? $payload['company']
            ?? Arr::get($customer, 'company')
            ?? ''
        ));

        if ($companyName === '') {
            throw ValidationException::withMessages([
                'company_name' => 'company_name is required for business subscriptions.',
            ]);
        }

        $billingEmail = trim((string) (
            $payload['company_billing_email']
            ?? Arr::get($customer, 'company_billing_email')
            ?? $ownerEmail
        ));

        return [
            'company_name' => $companyName,
            'billing_email' => $billingEmail !== '' ? strtolower($billingEmail) : null,
            'phone' => $this->nullableString($payload['company_phone'] ?? Arr::get($customer, 'company_phone') ?? ($payload['phone'] ?? Arr::get($customer, 'phone'))),
            'city' => $this->nullableString($payload['company_city'] ?? Arr::get($customer, 'company_city') ?? Arr::get($customer, 'city')),
            'country' => $this->nullableString($payload['company_country'] ?? Arr::get($customer, 'company_country') ?? Arr::get($customer, 'country')),
        ];
    }

    /**
     * @param  array{
     *     company_name: string,
     *     billing_email: ?string,
     *     phone: ?string,
     *     city: ?string,
     *     country: ?string,
     * }  $context
     */
    public function upsertCompany(array $context, Plan $plan, User $owner): Company
    {
        $company = Company::query()->where('name', $context['company_name'])->first();

        if (! $company) {
            $company = Company::create([
                'name' => $context['company_name'],
                'billing_email' => $context['billing_email'] ?? $owner->email,
                'phone' => $context['phone'],
                'city' => $context['city'],
                'country' => $context['country'],
                'default_plan_id' => $plan->id,
                'owner_user_id' => $owner->id,
            ]);
        } else {
            $company->fill([
                'billing_email' => $context['billing_email'] ?? $company->billing_email ?? $owner->email,
                'phone' => $context['phone'] ?? $company->phone,
                'city' => $context['city'] ?? $company->city,
                'country' => $context['country'] ?? $company->country,
                'default_plan_id' => $plan->id,
            ]);

            if (! $company->owner_user_id) {
                $company->owner_user_id = $owner->id;
            }

            if ($company->isDirty()) {
                $company->save();
            }
        }

        return $company->fresh();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $customer
     * @param  array<string, mixed>  $billingTimeline
     */
    public function syncCompanyBillingMembership(
        array $payload,
        array $customer,
        User $owner,
        Company $company,
        Plan $plan,
        string $subscriptionId,
        string $membershipNumber,
        string $status,
        ?Carbon $coverageStart,
        ?Carbon $coverageEnd,
        array $billingTimeline,
        string $billingProvider,
    ): Membership {
        $customerId = (string) ($payload['customer_id'] ?? Arr::get($customer, 'customer_id') ?? '');

        $membership = Membership::query()->updateOrCreate(
            ['billing_subscription_id' => $subscriptionId],
            [
                'membership_number' => $membershipNumber,
                'plan_id' => $plan->id,
                'account_user_id' => $owner->id,
                'company_id' => $company->id,
                'partner_id' => null,
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

        $this->syncPrimaryMemberFromCustomer($membership, $customer, $owner);
        $this->billingService->recalculate($company);

        return $membership;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $customer
     * @param  array<string, mixed>  $billingTimeline
     */
    public function syncEmployeeMembership(
        array $payload,
        array $customer,
        Company $company,
        Plan $plan,
        string $subscriptionId,
        string $membershipNumber,
        string $status,
        ?Carbon $coverageStart,
        ?Carbon $coverageEnd,
        array $billingTimeline,
        string $billingProvider,
        ?User $linkedUser = null,
    ): Membership {
        $customerId = (string) ($payload['customer_id'] ?? Arr::get($customer, 'customer_id') ?? '');

        $membership = Membership::query()->updateOrCreate(
            ['billing_subscription_id' => $subscriptionId],
            [
                'membership_number' => $membershipNumber,
                'plan_id' => $plan->id,
                'account_user_id' => null,
                'company_id' => $company->id,
                'partner_id' => null,
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

        $this->syncPrimaryMemberFromCustomer($membership, $customer, $linkedUser);
        $this->billingService->recalculate($company);

        return $membership;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $customer
     */
    public function resolveCompanyForEmployeePayload(array $payload, array $customer): Company
    {
        $companyName = trim((string) (
            $payload['company_name']
            ?? Arr::get($customer, 'company_name')
            ?? $payload['company']
            ?? ''
        ));

        if ($companyName === '') {
            throw ValidationException::withMessages([
                'company_name' => 'company_name is required for b2b_employee subscriptions.',
            ]);
        }

        $company = Company::query()->where('name', $companyName)->first();
        if (! $company) {
            throw ValidationException::withMessages([
                'company_name' => "No portal company matches \"{$companyName}\".",
            ]);
        }

        return $company;
    }

    public function ensureBusinessUser(User $user): void
    {
        if (! $user->hasRole('business')) {
            $user->assignRole('business');
        }
    }

    public function ensureCustomerUser(User $user): void
    {
        if (! $user->hasRole('customer')) {
            $user->assignRole('customer');
        }
    }

    /**
     * @param  array<string, mixed>  $customer
     */
    public function syncPrimaryMemberFromCustomer(Membership $membership, array $customer, ?User $user = null): void
    {
        $display = trim((string) (Arr::get($customer, 'display_name') ?: $user?->name ?: ''));
        $parts = preg_split('/\s+/', $display, 2, PREG_SPLIT_NO_EMPTY) ?: [];
        $first = trim((string) (Arr::get($customer, 'first_name') ?: ($parts[0] ?? 'Member')));
        $last = trim((string) (Arr::get($customer, 'last_name') ?: ($parts[1] ?? 'Member')));

        $email = $user?->email;
        if ($email === null) {
            $rawEmail = Arr::get($customer, 'email');
            $email = is_string($rawEmail) && filter_var($rawEmail, FILTER_VALIDATE_EMAIL)
                ? strtolower(trim($rawEmail))
                : null;
        }

        $primary = Member::query()->firstOrNew([
            'membership_id' => $membership->id,
            'is_primary' => true,
        ]);

        $primary->fill([
            'first_name' => $first,
            'last_name' => $last,
            'email' => $email,
            'phone' => $this->nullableString(Arr::get($customer, 'phone')),
            'country' => $this->nullableString(Arr::get($customer, 'country')) ?: $primary->country,
            'city' => $this->nullableString(Arr::get($customer, 'city')) ?: $primary->city,
        ]);

        if (! $primary->qr_token) {
            $primary->qr_token = (string) Str::uuid();
        }

        $primary->save();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function inferAutoRenew(array $payload): bool
    {
        $scheduledCancel = trim((string) ($payload['scheduled_cancellation_date'] ?? ''));
        if ($scheduledCancel !== '') {
            return false;
        }

        $status = strtolower((string) ($payload['status'] ?? ''));
        if (in_array($status, ['cancelled', 'canceled', 'expired'], true)) {
            return false;
        }

        return true;
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed !== '' ? $trimmed : null;
    }
}
