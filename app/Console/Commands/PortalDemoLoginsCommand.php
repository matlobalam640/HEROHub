<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PortalDemoLoginsCommand extends Command
{
    protected $signature = 'portal:demo-logins';

    protected $description = 'Print demo portal accounts and password (from DemoDataSeeder; local/staging only).';

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('Not available in production.');

            return self::FAILURE;
        }

        $this->line('');
        $this->info('Demo data (after php artisan migrate --seed)');
        $this->line('Password for all accounts below: <fg=yellow;options=bold>password</>');
        $this->line('');
        $this->table(
            ['Role', 'Email'],
            [
                ['admin', 'admin@demo.herohub.local'],
                ['dispatch', 'dispatch@demo.herohub.local'],
                ['partner', 'partner@demo.herohub.local'],
                ['business', 'business@demo.herohub.local'],
                ['customer', 'customer1@demo.herohub.local'],
                ['customer', 'customer2@demo.herohub.local'],
                ['…', 'customer3@demo.herohub.local … customer12@demo.herohub.local'],
            ]
        );
        $this->line('');
        $this->comment('Memberships: DemoDataSeeder creates HERO-000001… style memberships for customer1–12.');
        $this->comment('BindDemoMembershipSeeder: set HERO_BIND_MEMBERSHIP_EMAIL in .env to attach HR-02 demo membership to that user.');
        $this->comment('Zoho webhook auto-created users: password is random — use the welcome email “Create password” link or Forgot password.');
        $this->line('');

        return self::SUCCESS;
    }
}
