<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class MailTestCommand extends Command
{
    protected $signature = 'mail:test
                            {email? : Recipient address (defaults to MAIL_FROM_ADDRESS)}
                            {--force : Allow sending when APP_ENV is production}';

    protected $description = 'Send a short test message using the configured mail transport (e.g. phpmail).';

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

        $this->info('Mailer: '.config('mail.default'));

        Mail::raw('HERO Hub mail test — if you received this, your mail configuration is working.', function ($message) use ($to): void {
            $message->to($to)->subject('HERO Hub mail test');
        });

        $this->info("Sent test message to {$to}.");

        return self::SUCCESS;
    }
}
