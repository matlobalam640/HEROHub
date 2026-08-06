<?php

namespace App\Console\Commands;

use App\Http\Services\UsaPayments\UsaPaymentResponse;
use App\Models\PaymentsLog;
use App\Models\Plan;
use App\Services\HeroPortal\HeroPortalWebhookService;
use Illuminate\Console\Command;

class BackfillHeroPortalWebhooksCommand extends Command
{
    protected $signature = 'hero:backfill-portal-webhooks {--limit=100 : Maximum number of payment logs to sync}';

    protected $description = 'Replay successful payment logs to the HERO membership portal webhook.';

    public function handle(HeroPortalWebhookService $webhookService): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $logs = PaymentsLog::query()
            ->where('response_code', '100')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        $sent = 0;
        foreach ($logs as $log) {
            $plan = Plan::query()->where('plan_id', $log->plan_id)->first();
            if (! $plan) {
                $this->warn("Skipping log {$log->id}: plan {$log->plan_id} not found.");

                continue;
            }

            $subscriptionId = trim((string) ($log->subscription_id ?? ''));
            if ($subscriptionId === '' && is_string($log->response)) {
                parse_str($log->response, $parsedFromResponse);
                $subscriptionId = trim((string) ($parsedFromResponse['subscription_id'] ?? ''));
            }

            if ($subscriptionId === '') {
                $this->warn("Skipping log {$log->id}: no subscription_id.");

                continue;
            }

            $nameParts = preg_split('/\s+/', trim((string) $log->user_name), 2) ?: [];
            $requestData = [
                'first_name' => $nameParts[0] ?? 'Member',
                'last_name' => $nameParts[1] ?? 'Member',
                'email' => $log->email,
                'phone' => '',
                'street' => '',
                'city' => '',
                'state' => '',
                'zip_code' => '',
                'country' => 'USA',
            ];

            $webhookService->notifySuccessfulSubscription(
                $plan,
                $requestData,
                new UsaPaymentResponse($log->response),
            );

            $sent++;
            $this->line("Synced log {$log->id} ({$log->email}) subscription {$subscriptionId}");
        }

        $this->info("Finished. Attempted {$sent} webhook call(s).");

        return self::SUCCESS;
    }
}
