<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Member;
use App\Models\MemberDependent;
use App\Models\Membership;
use App\Models\Partner;
use App\Models\PartnerSale;
use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Rich demo dataset for local/staging.
     *
     * All demo accounts use password: password
     * — admin@demo.herohub.local, dispatch@demo.herohub.local, partner@demo.herohub.local,
     *   business@demo.herohub.local, customer1@demo.herohub.local … customer12@demo.herohub.local
     */
    public function run(): void
    {
        $password = Hash::make('password');

        DB::transaction(function () use ($password) {
            $planDefs = [
                ['code' => 'SMB_TEAM', 'name' => 'Small Business Team', 'category' => 'business', 'min_members' => 5, 'max_members' => 25, 'price' => 199.00, 'billing_interval' => 'monthly'],
                ['code' => 'ENTERPRISE', 'name' => 'Enterprise Coverage', 'category' => 'business', 'min_members' => 25, 'max_members' => 500, 'price' => 899.00, 'billing_interval' => 'yearly'],
            ];

            $plans = [];
            foreach ($planDefs as $row) {
                $plans[$row['code']] = Plan::updateOrCreate(
                    ['code' => $row['code']],
                    array_merge($row, ['currency' => 'USD', 'active' => true])
                );
            }

            foreach (['HR-01A', 'HR-01AC', 'HR-01B', 'HR-01BC', 'HR-02', 'HR-02C', 'HR-03', 'HR-03C'] as $code) {
                $retail = Plan::query()->where('code', $code)->first();
                if ($retail) {
                    $plans[$code] = $retail;
                }
            }

            $admin = User::firstOrCreate(
                ['email' => 'admin@demo.herohub.local'],
                ['name' => 'HERO Admin', 'password' => $password, 'email_verified_at' => now()]
            );
            $admin->syncRoles(['admin']);

            $dispatch = User::firstOrCreate(
                ['email' => 'dispatch@demo.herohub.local'],
                ['name' => 'Dispatch Team', 'password' => $password, 'email_verified_at' => now()]
            );
            $dispatch->syncRoles(['dispatch']);

            $partnerUser = User::firstOrCreate(
                ['email' => 'partner@demo.herohub.local'],
                ['name' => 'Alex Partner', 'password' => $password, 'email_verified_at' => now()]
            );
            $partnerUser->syncRoles(['partner']);

            $businessUser = User::firstOrCreate(
                ['email' => 'business@demo.herohub.local'],
                ['name' => 'Riverfront HR', 'password' => $password, 'email_verified_at' => now()]
            );
            $businessUser->syncRoles(['business']);

            $companyRiver = Company::firstOrCreate(
                ['name' => 'Riverfront Logistics Inc.'],
                [
                    'billing_email' => 'billing@riverfrontdemo.test',
                    'phone' => '+1 555 010 2200',
                    'country' => 'USA',
                    'city' => 'Chicago',
                    'address_line1' => '1200 W River Rd',
                    'postal_code' => '60601',
                    'owner_user_id' => $businessUser->id,
                ]
            );

            $companyMedical = Company::firstOrCreate(
                ['name' => 'Great Lakes Medical Group'],
                [
                    'billing_email' => 'ap@glmed.demo',
                    'phone' => '+1 555 010 3301',
                    'country' => 'USA',
                    'city' => 'Detroit',
                    'address_line1' => '88 Health Park',
                    'postal_code' => '48201',
                    'owner_user_id' => $businessUser->id,
                ]
            );

            $companyRiver->forceFill([
                'default_plan_id' => $plans['SMB_TEAM']->id,
            ])->save();
            $companyMedical->forceFill([
                'default_plan_id' => $plans['ENTERPRISE']->id,
            ])->save();

            $partnerSummit = Partner::firstOrCreate(
                ['name' => 'Summit Brokers LLC'],
                ['user_id' => $partnerUser->id, 'commission_percent' => 8.00, 'active' => true]
            );

            $partnerCoastal = Partner::firstOrCreate(
                ['name' => 'Coastal Enrollment Partners'],
                ['user_id' => null, 'commission_percent' => 10.00, 'active' => true]
            );

            $customerUsers = [];
            $firstNames = ['Alice', 'Bob', 'Carla', 'Diego', 'Elena', 'Frank', 'Grace', 'Hassan', 'Ivy', 'Jamal', 'Kim', 'Leo'];
            $lastNames = ['Johnson', 'Smith', 'Nguyen', 'Reyes', 'Park', 'Okafor', 'Chen', 'Abbas', 'Miller', 'Brown', 'Singh', 'Vogel'];

            foreach (range(0, 11) as $i) {
                $n = $i + 1;
                $u = User::firstOrCreate(
                    ['email' => "customer{$n}@demo.herohub.local"],
                    [
                        'name' => "{$firstNames[$i]} {$lastNames[$i]}",
                        'password' => $password,
                        'email_verified_at' => now(),
                    ]
                );
                $u->syncRoles(['customer']);
                $customerUsers[] = $u;
            }

            $specs = [
                ['plan' => 'HR-02', 'user_idx' => 0, 'company' => null, 'partner' => $partnerSummit, 'status' => 'active', 'months_ago' => 0],
                ['plan' => 'HR-03', 'user_idx' => 1, 'company' => null, 'partner' => $partnerSummit, 'status' => 'active', 'months_ago' => 1],
                ['plan' => 'HR-01A', 'user_idx' => 2, 'company' => null, 'partner' => $partnerCoastal, 'status' => 'inactive', 'months_ago' => 2],
                ['plan' => 'HR-02', 'user_idx' => 3, 'company' => null, 'partner' => null, 'status' => 'active', 'months_ago' => 3],
                ['plan' => 'HR-03', 'user_idx' => 4, 'company' => null, 'partner' => $partnerCoastal, 'status' => 'expired', 'months_ago' => 4],
                ['plan' => 'HR-02', 'user_idx' => 5, 'company' => null, 'partner' => $partnerSummit, 'status' => 'active', 'months_ago' => 5],
                ['plan' => 'SMB_TEAM', 'user_idx' => null, 'company' => $companyRiver, 'partner' => $partnerSummit, 'status' => 'active', 'months_ago' => 6],
                ['plan' => 'ENTERPRISE', 'user_idx' => null, 'company' => $companyMedical, 'partner' => $partnerCoastal, 'status' => 'active', 'months_ago' => 7],
                ['plan' => 'HR-02', 'user_idx' => 6, 'company' => null, 'partner' => null, 'status' => 'cancelled', 'months_ago' => 8],
                ['plan' => 'HR-03', 'user_idx' => 7, 'company' => null, 'partner' => $partnerSummit, 'status' => 'active', 'months_ago' => 9],
                ['plan' => 'HR-02', 'user_idx' => 8, 'company' => null, 'partner' => $partnerCoastal, 'status' => 'active', 'months_ago' => 10],
                ['plan' => 'HR-01A', 'user_idx' => 9, 'company' => null, 'partner' => null, 'status' => 'active', 'months_ago' => 11],
                ['plan' => 'SMB_TEAM', 'user_idx' => null, 'company' => $companyRiver, 'partner' => $partnerSummit, 'status' => 'inactive', 'months_ago' => 2],
                ['plan' => 'HR-02', 'user_idx' => 10, 'company' => null, 'partner' => null, 'status' => 'active', 'months_ago' => 1],
                ['plan' => 'HR-03', 'user_idx' => 11, 'company' => null, 'partner' => $partnerCoastal, 'status' => 'active', 'months_ago' => 0],
                ['plan' => 'HR-02', 'user_idx' => 0, 'company' => null, 'partner' => null, 'status' => 'active', 'months_ago' => 3],
                ['plan' => 'ENTERPRISE', 'user_idx' => null, 'company' => $companyMedical, 'partner' => $partnerSummit, 'status' => 'active', 'months_ago' => 0],
                ['plan' => 'HR-02', 'user_idx' => 4, 'company' => null, 'partner' => $partnerCoastal, 'status' => 'active', 'months_ago' => 10],
            ];

            $membershipModels = [];
            $n = 1;
            foreach ($specs as $spec) {
                $plan = $plans[$spec['plan']];
                $created = Carbon::now()->subMonths($spec['months_ago'])->subDays(random_int(0, 20));

                $accountId = $spec['user_idx'] !== null
                    ? $customerUsers[$spec['user_idx']]->id
                    : null;

                $membership = Membership::create([
                    'membership_number' => 'HERO-'.str_pad((string) $n++, 6, '0', STR_PAD_LEFT),
                    'plan_id' => $plan->id,
                    'account_user_id' => $accountId,
                    'company_id' => $spec['company']?->id,
                    'partner_id' => $spec['partner']?->id,
                    'coverage_starts_on' => $created->copy()->subDays(5),
                    'coverage_ends_on' => $created->copy()->addYear(),
                    'auto_renew' => true,
                    'status' => $spec['status'],
                ]);

                $membership->forceFill([
                    'created_at' => $created,
                    'updated_at' => $created,
                ])->save();

                $membershipModels[] = $membership;

                $primaryFirst = $spec['user_idx'] !== null
                    ? explode(' ', $customerUsers[$spec['user_idx']]->name)[0]
                    : 'Employee';
                $primaryLast = $spec['user_idx'] !== null
                    ? explode(' ', $customerUsers[$spec['user_idx']]->name)[1] ?? 'Member'
                    : 'Primary';

                Member::create([
                    'membership_id' => $membership->id,
                    'is_primary' => true,
                    'first_name' => $primaryFirst,
                    'last_name' => $primaryLast,
                    'date_of_birth' => '1985-05-15',
                    'gender' => 'unspecified',
                    'phone' => '+1 555 '.str_pad((string) random_int(100, 999), 3, '0', STR_PAD_LEFT).' '.str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT),
                    'email' => $accountId ? $customerUsers[$spec['user_idx']]->email : 'employee'.$membership->id.'@company.demo',
                    'id_number' => 'DL-'.strtoupper(Str::random(8)),
                    'country' => 'USA',
                    'city' => 'Chicago',
                    'qr_token' => (string) Str::uuid(),
                ]);

                if ($spec['plan'] === 'HR-03' && random_int(0, 1) === 1) {
                    MemberDependent::create([
                        'membership_id' => $membership->id,
                        'relationship' => 'child',
                        'first_name' => 'Sam',
                        'last_name' => $primaryLast,
                        'date_of_birth' => '2012-03-01',
                        'gender' => 'unspecified',
                        'phone' => null,
                    ]);
                }
            }

            foreach ($membershipModels as $idx => $membership) {
                if (! $membership->partner_id) {
                    continue;
                }
                $soldAt = Carbon::parse($membership->created_at)->addDays(random_int(0, 5));
                $planRow = $plans[$specs[$idx]['plan']];
                $price = (float) ($planRow->price ?? 49);
                $pct = $membership->partner_id === $partnerSummit->id ? 8.0 : 10.0;
                $sale = PartnerSale::create([
                    'partner_id' => $membership->partner_id,
                    'membership_id' => $membership->id,
                    'plan_id' => $membership->plan_id,
                    'sale_amount' => $price,
                    'commission_percent' => $pct,
                    'commission_amount' => round($price * ($pct / 100), 2),
                    'sold_at' => $soldAt,
                ]);
                $sale->forceFill([
                    'created_at' => $soldAt,
                    'updated_at' => $soldAt,
                ])->save();
            }

            foreach (range(1, 10) as $k) {
                $p = ($k % 2 === 0) ? $partnerSummit : $partnerCoastal;
                $m = $membershipModels[array_rand($membershipModels)];
                $soldAt = Carbon::now()->subMonths(random_int(0, 5))->subDays(random_int(0, 27));
                $planRow = Plan::find($m->plan_id);
                $price = (float) ($planRow?->price ?? 99);
                $pct = $p->id === $partnerSummit->id ? 8.0 : 10.0;
                $sale = PartnerSale::create([
                    'partner_id' => $p->id,
                    'membership_id' => $m->id,
                    'plan_id' => $m->plan_id,
                    'sale_amount' => $price,
                    'commission_percent' => $pct,
                    'commission_amount' => round($price * ($pct / 100), 2),
                    'sold_at' => $soldAt,
                ]);
                $sale->forceFill([
                    'created_at' => $soldAt,
                    'updated_at' => $soldAt,
                ])->save();
            }
        });
    }
}
