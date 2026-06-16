<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Aligns the latest membership for customer1@demo.herohub.local with a sample
 * Zoho Billing subscription payload (VIP family yearly / HR-03CY).
 *
 * Run: php artisan db:seed --class=UpdateCustomer1DemoMembershipFromZohoSampleSeeder
 */
class UpdateCustomer1DemoMembershipFromZohoSampleSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->where('email', 'customer1@demo.herohub.local')->first();
        if (! $user) {
            $this->command?->warn('User customer1@demo.herohub.local not found.');

            return;
        }

        $plan = Plan::query()
            ->where(function ($q): void {
                $q->where('zoho_code_yearly', 'HR-03CY')
                    ->orWhere('zoho_code_monthly', 'HR-03CY');
            })
            ->first();

        if (! $plan) {
            $this->command?->warn('No plan with Zoho code HR-03CY. Run: php artisan db:seed --class=RetailPlansSeeder');

            return;
        }

        $membership = Membership::query()
            ->where('account_user_id', $user->id)
            ->orderByDesc('id')
            ->first();

        if (! $membership) {
            $this->command?->warn('No membership row for customer1@demo.herohub.local.');

            return;
        }

        $desiredNumber = 'ZOHO-SUB-00107';
        if (Membership::query()->where('membership_number', $desiredNumber)->where('id', '!=', $membership->id)->exists()) {
            $desiredNumber = $membership->membership_number;
            $this->command?->warn("membership_number {$desiredNumber} already taken elsewhere; keeping existing number on this row.");
        }

        $membership->update([
            'membership_number' => $desiredNumber,
            'plan_id' => $plan->id,
            'billing_provider' => 'zoho',
            'billing_customer_id' => '6304056000000152937',
            'billing_subscription_id' => '6304056000000755001',
            'billing_subscription_created_at' => Carbon::parse('2026-04-24T12:34:26-0400'),
            'billing_next_billing_at' => null,
            'billing_last_billing_at' => Carbon::parse('2026-04-24')->startOfDay(),
            'billing_auto_collect' => false,
            'coverage_starts_on' => Carbon::parse('2026-04-24')->startOfDay(),
            'coverage_ends_on' => Carbon::parse('2027-04-24')->startOfDay(),
            'status' => 'active',
            'auto_renew' => true,
        ]);

        $primary = Member::query()
            ->where('membership_id', $membership->id)
            ->where('is_primary', true)
            ->first();

        if ($primary) {
            $primary->update([
                'first_name' => 'Katia',
                'last_name' => 'Brezault',
                'email' => $user->email,
            ]);
        }

        $user->update([
            'name' => 'Katia Brezault',
        ]);

        $this->command?->info("Updated membership #{$membership->id} for customer1@demo.herohub.local (plan {$plan->code}, Zoho SUB-00107).");
    }
}
