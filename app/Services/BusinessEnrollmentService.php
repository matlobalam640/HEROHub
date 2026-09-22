<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use App\Support\PortalInvite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BusinessEnrollmentService
{
    public const STATUS_PENDING_PAYMENT = 'pending_payment';

    public const STATUS_ACTIVE = 'active';

    /**
     * @param  array{
     *     company_name: string,
     *     billing_email?: ?string,
     *     company_phone?: ?string,
     *     company_city?: ?string,
     *     company_country?: ?string,
     *     address_line1?: ?string,
     *     postal_code?: ?string,
     *     hr_first_name: string,
     *     hr_last_name: string,
     *     hr_email: string,
     *     hr_phone?: ?string,
     * }  $data
     * @return array{company: Company, owner: User, owner_created: bool}
     */
    public function enrollPendingCompany(array $data): array
    {
        $companyName = trim($data['company_name']);
        $hrEmail = strtolower(trim($data['hr_email']));

        if ($companyName === '' || $hrEmail === '') {
            throw ValidationException::withMessages([
                'company_name' => 'Company name and HR email are required.',
            ]);
        }

        $duplicate = Company::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($companyName)])
            ->exists();
        if ($duplicate) {
            throw ValidationException::withMessages([
                'company_name' => 'A company with this name already exists.',
            ]);
        }

        return DB::transaction(function () use ($data, $companyName, $hrEmail) {
            [$owner, $ownerCreated] = $this->resolveOrCreateHrOwner(
                email: $hrEmail,
                firstName: trim($data['hr_first_name']),
                lastName: trim($data['hr_last_name']),
            );

            $company = Company::create([
                'name' => $companyName,
                'billing_email' => isset($data['billing_email']) && trim((string) $data['billing_email']) !== ''
                    ? strtolower(trim((string) $data['billing_email']))
                    : $hrEmail,
                'phone' => isset($data['company_phone']) ? trim((string) $data['company_phone']) ?: null : null,
                'city' => isset($data['company_city']) ? trim((string) $data['company_city']) ?: null : null,
                'country' => isset($data['company_country']) ? trim((string) $data['company_country']) ?: null : null,
                'address_line1' => isset($data['address_line1']) ? trim((string) $data['address_line1']) ?: null : null,
                'postal_code' => isset($data['postal_code']) ? trim((string) $data['postal_code']) ?: null : null,
                'owner_user_id' => $owner->id,
                'enrollment_status' => self::STATUS_PENDING_PAYMENT,
                'invited_at' => now(),
            ]);

            $this->sendInvites($company, $owner, $ownerCreated);

            return [
                'company' => $company->fresh(['ownerUser']),
                'owner' => $owner,
                'owner_created' => $ownerCreated,
            ];
        });
    }

    /**
     * @return array{0: User, 1: bool}
     */
    private function resolveOrCreateHrOwner(string $email, string $firstName, string $lastName): array
    {
        $user = User::query()->where('email', $email)->first();

        if ($user) {
            if ($user->hasRole('admin')) {
                throw ValidationException::withMessages([
                    'hr_email' => 'This email cannot be used as an HR portal owner.',
                ]);
            }

            $alreadyOwns = Company::query()->where('owner_user_id', $user->id)->exists();
            if ($alreadyOwns) {
                throw ValidationException::withMessages([
                    'hr_email' => 'This person already owns another company portal account.',
                ]);
            }

            if (! $user->hasRole('business')) {
                $user->assignRole('business');
            }

            if (trim($user->name) === '') {
                $user->forceFill(['name' => trim($firstName.' '.$lastName)])->save();
            }

            return [$user, false];
        }

        $user = User::create([
            'name' => trim($firstName.' '.$lastName),
            'email' => $email,
            'password' => Hash::make(Str::password(32)),
            'email_verified_at' => now(),
        ]);
        $user->assignRole('business');

        return [$user, true];
    }

    private function sendInvites(Company $company, User $owner, bool $ownerCreated): void
    {
        $setupUrl = $ownerCreated
            ? PortalInvite::passwordSetupUrl($owner)
            : route('login', [], true);

        PortalInvite::sendUserInvite(
            user: $owner,
            subject: 'Your HERO company portal invitation',
            headline: 'Your company portal is ready — set your password to continue.',
            detailLines: [
                'Company: '.$company->name,
                'Next step: sign in, choose a Small Business or Corporate plan, and pay with USA Payments to activate coverage.',
                'Until payment is complete, employee enrollment stays locked.',
            ],
            actionUrl: $setupUrl,
            actionLabel: $ownerCreated ? 'Create your portal password' : 'Sign in to company portal',
        );

        PortalInvite::notifyAdmins(
            subject: 'Admin alert: new B2B company pending payment',
            headline: 'A business was enrolled and is awaiting plan selection and payment.',
            detailLines: [
                'Company: '.$company->name,
                'HR contact: '.$owner->name.' <'.$owner->email.'>',
                'Billing email: '.($company->billing_email ?: '—'),
                'Phone: '.($company->phone ?: '—'),
                'Status: pending payment',
            ],
            actionUrl: route('admin.companies.index', [], true),
            actionLabel: 'Open companies list',
        );
    }
}
