<?php

namespace App\Console\Commands;

use App\Services\MembershipUsaPaymentsDateSyncService;
use App\Services\UsaPayments\UsaPaymentsQueryService;
use Illuminate\Console\Command;

class SyncMembershipDatesFromUsaPaymentsCommand extends Command
{
    protected $signature = 'memberships:sync-dates-from-usa-payments
                            {--subscription-id= : Sync a single USA Payments subscription id}
                            {--dry-run : Show resolved dates without writing to the database}';

    protected $description = 'Pull actual coverage and billing dates from USA Payments (AWS gateway source) into portal memberships.';

    public function handle(
        UsaPaymentsQueryService $queryService,
        MembershipUsaPaymentsDateSyncService $syncService,
    ): int {
        if (! config('usa_payments.security_key')) {
            $this->error('USA_PAYMENTS_SECURITY_KEY is not configured.');

            return self::FAILURE;
        }

        $apply = ! $this->option('dry-run');
        $singleId = trim((string) $this->option('subscription-id'));

        $subscriptions = $singleId !== ''
            ? array_filter([$queryService->getRecurringSubscription($singleId)])
            : $queryService->listRecurringSubscriptions();

        if ($subscriptions === []) {
            $this->warn('No subscriptions returned from USA Payments.');

            return self::SUCCESS;
        }

        $matched = 0;
        $updated = 0;
        $unmatched = 0;

        $this->table(
            ['Subscription', 'Email', 'Membership', 'Start', 'End', 'Status', 'Applied'],
            collect($subscriptions)->map(function (array $subscription) use ($syncService, $apply, &$matched, &$updated, &$unmatched) {
                $row = $syncService->syncSubscription($subscription, $apply);

                if ($row['matched']) {
                    $matched++;
                } else {
                    $unmatched++;
                }

                if ($row['updated']) {
                    $updated++;
                }

                return [
                    $row['subscription_id'],
                    $row['email'],
                    $row['membership_number'] ?? '—',
                    $row['coverage_starts_on'] ?? '—',
                    $row['coverage_ends_on'] ?? '—',
                    $row['status'],
                    $apply ? ($row['updated'] ? 'yes' : 'no') : 'dry-run',
                ];
            })->all()
        );

        $this->newLine();
        $this->line('Subscriptions fetched: '.count($subscriptions));
        $this->line('Matched portal memberships: '.$matched);
        $this->line('Unmatched subscriptions: '.$unmatched);
        $this->line(($apply ? 'Updated' : 'Would update').' memberships: '.$updated);

        return self::SUCCESS;
    }
}
