<?php

namespace App\Console\Commands;

use App\Services\MembershipUsaPaymentsDateSyncService;
use App\Services\UsaPayments\UsaPaymentsQueryService;
use Illuminate\Console\Command;

class SyncMembershipDatesFromUsaPaymentsCommand extends Command
{
    protected $signature = 'memberships:sync-dates-from-usa-payments
                            {--subscription-id= : Sync a single USA Payments subscription id}
                            {--dry-run : Show resolved dates without writing to the database}
                            {--fail-on-zero-updates : Exit with failure when no memberships were updated}';

    protected $description = 'Pull actual coverage and billing dates from USA Payments (AWS gateway source) into portal memberships.';

    public function handle(
        UsaPaymentsQueryService $queryService,
        MembershipUsaPaymentsDateSyncService $syncService,
    ): int {
        if (! config('usa_payments.security_key')) {
            $this->error('USA_PAYMENTS_SECURITY_KEY is not configured on this server.');

            return self::FAILURE;
        }

        $apply = ! $this->option('dry-run');
        $singleId = trim((string) $this->option('subscription-id'));

        $subscriptions = $singleId !== ''
            ? array_filter([$queryService->getRecurringSubscription($singleId)])
            : $queryService->listRecurringSubscriptions();

        if ($subscriptions === []) {
            $this->error('No subscriptions returned from USA Payments. Check USA_PAYMENTS_SECURITY_KEY and outbound API access.');

            return self::FAILURE;
        }

        $rows = $singleId !== ''
            ? collect($subscriptions)->map(fn (array $subscription) => $syncService->syncSubscription($subscription, $apply))->all()
            : $syncService->syncAllPortalMemberships($subscriptions, $apply);

        $matched = collect($rows)->where('matched', true)->count();
        $updated = collect($rows)->where('updated', true)->count();
        $unmatched = collect($rows)->where('matched', false)->count();

        $this->table(
            ['Membership', 'Email', 'Subscription', 'Start', 'End', 'Status', 'Applied', 'Note'],
            collect($rows)->map(function (array $row) use ($apply) {
                return [
                    $row['membership_number'] ?? '—',
                    $row['email'] ?? '—',
                    $row['subscription_id'] ?? '—',
                    $row['coverage_starts_on'] ?? '—',
                    $row['coverage_ends_on'] ?? '—',
                    $row['status'] ?? '—',
                    $apply ? (($row['updated'] ?? false) ? 'yes' : 'no') : 'dry-run',
                    $row['note'] ?? '',
                ];
            })->all()
        );

        $this->newLine();
        $this->line('USA Payments subscriptions fetched: '.count($subscriptions));
        $this->line('Portal memberships matched: '.$matched);
        $this->line('Portal memberships unmatched: '.$unmatched);
        $this->line(($apply ? 'Updated' : 'Would update').' memberships: '.$updated);

        if ($this->option('fail-on-zero-updates') && $apply && $updated === 0) {
            $this->error('No memberships were updated.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
