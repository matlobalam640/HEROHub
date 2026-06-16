<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Membership;
use App\Models\Partner;
use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Links an active membership to portal accounts that have none yet (e.g. admin testing the card).
 * Requires plans from DemoDataSeeder (run full db:seed first, or DemoDataSeeder before this).
 */
class BindDemoMembershipSeeder extends Seeder
{
    public function run(): void
    {
        $plan = Plan::where('code', 'HR-02')->first();
        if (! $plan) {
            $this->command->warn('No HR-02 plan found. Run: php artisan db:seed (includes RetailPlansSeeder).');

            return;
        }

        $partner = Partner::query()->first();

        $emails = array_unique(array_filter(array_map('trim', [
            'admin@demo.herohub.local',
            'business@demo.herohub.local',
            (string) env('HERO_BIND_MEMBERSHIP_EMAIL'),
        ])));

        foreach ($emails as $email) {
            $user = User::where('email', $email)->first();
            if (! $user) {
                continue;
            }
            if (Membership::where('account_user_id', $user->id)->exists()) {
                continue;
            }

            $membershipNumber = 'HERO-DEMO-'.str_pad((string) $user->id, 5, '0', STR_PAD_LEFT);

            $membership = Membership::create([
                'membership_number' => $membershipNumber,
                'plan_id' => $plan->id,
                'account_user_id' => $user->id,
                'company_id' => null,
                'partner_id' => $partner?->id,
                'coverage_starts_on' => Carbon::now()->subMonths(2),
                'coverage_ends_on' => Carbon::now()->addMonths(10),
                'auto_renew' => true,
                'status' => 'active',
            ]);

            $parts = preg_split('/\s+/', trim($user->name), 2, PREG_SPLIT_NO_EMPTY) + [null, null];
            $first = $parts[0] ?? 'Demo';
            $last = $parts[1] ?? 'Member';

            Member::create([
                'membership_id' => $membership->id,
                'is_primary' => true,
                'first_name' => $first,
                'last_name' => $last,
                'date_of_birth' => '1990-01-15',
                'gender' => 'unspecified',
                'phone' => '+1 555 010 0000',
                'email' => $user->email,
                'id_number' => 'DL-DEMO-'.strtoupper(Str::random(6)),
                'country' => 'USA',
                'city' => 'Miami',
                'qr_token' => (string) Str::uuid(),
            ]);

            $this->command->info("Linked demo membership {$membershipNumber} to {$email}.");
        }
    }
}
