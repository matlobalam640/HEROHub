<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalGlobalSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $user = $request->user();

        if (! $user->hasAnyRole(['admin', 'dispatch'])) {
            return response()->json(['results' => []]);
        }

        $escaped = '%'.addcslashes($q, '%_\\').'%';
        $results = [];

        User::query()
            ->role('customer')
            ->where(function (Builder $query) use ($escaped) {
                $query->where('email', 'like', $escaped)
                    ->orWhere('name', 'like', $escaped);
            })
            ->orderBy('name')
            ->limit(5)
            ->get(['id', 'name', 'email'])
            ->each(function (User $customer) use (&$results) {
                $results[] = [
                    'kind' => 'customer',
                    'label' => $customer->name ?: $customer->email,
                    'meta' => $customer->email,
                    'url' => route('dispatch.verification', ['q' => $customer->email]),
                ];
            });

        Membership::query()
            ->with(['plan', 'members', 'accountUser'])
            ->where(function (Builder $query) use ($q, $escaped) {
                $query->where('membership_number', 'like', $escaped)
                    ->orWhereHas('members', fn (Builder $mq) => $this->applyMemberSearch($mq, $q, $escaped))
                    ->orWhereHas('dependents', fn (Builder $dq) => $this->applyDependentSearch($dq, $q, $escaped))
                    ->orWhereHas('accountUser', fn (Builder $uq) => $uq->where('email', 'like', $escaped)->orWhere('name', 'like', $escaped))
                    ->orWhereHas('company', fn (Builder $cq) => $cq->where('name', 'like', $escaped));
            })
            ->orderByDesc('id')
            ->limit(8)
            ->get()
            ->each(function (Membership $membership) use (&$results) {
                $primary = $membership->members->firstWhere('is_primary', true) ?? $membership->members->first();
                $holder = $primary
                    ? trim($primary->first_name.' '.$primary->last_name)
                    : ($membership->accountUser?->name ?? 'Member');

                $results[] = [
                    'kind' => 'membership',
                    'label' => $membership->membership_number,
                    'meta' => trim($holder.' · '.ucfirst($membership->status).($membership->plan ? ' · '.$membership->plan->name : '')),
                    'url' => route('portal.membership.show', $membership),
                ];
            });

        $unique = [];
        $seen = [];

        foreach ($results as $row) {
            if (isset($seen[$row['url']])) {
                continue;
            }
            $seen[$row['url']] = true;
            $unique[] = $row;
            if (count($unique) >= 10) {
                break;
            }
        }

        return response()->json(['results' => $unique]);
    }

    private function applyMemberSearch(Builder $query, string $q, string $escaped): void
    {
        $query->where(function (Builder $mq) use ($q, $escaped) {
            $mq->where('first_name', 'like', $escaped)
                ->orWhere('last_name', 'like', $escaped)
                ->orWhere('phone', 'like', $escaped)
                ->orWhere('email', 'like', $escaped);

            $parts = preg_split('/\s+/u', trim($q), -1, PREG_SPLIT_NO_EMPTY);
            if (count($parts) >= 2) {
                $first = '%'.addcslashes($parts[0], '%_\\').'%';
                $last = '%'.addcslashes(implode(' ', array_slice($parts, 1)), '%_\\').'%';
                $mq->orWhere(function (Builder $pair) use ($first, $last) {
                    $pair->where('first_name', 'like', $first)
                        ->where('last_name', 'like', $last);
                });
            }
        });
    }

    private function applyDependentSearch(Builder $query, string $q, string $escaped): void
    {
        $query->where(function (Builder $dq) use ($q, $escaped) {
            $dq->where('first_name', 'like', $escaped)
                ->orWhere('last_name', 'like', $escaped)
                ->orWhere('phone', 'like', $escaped);

            $parts = preg_split('/\s+/u', trim($q), -1, PREG_SPLIT_NO_EMPTY);
            if (count($parts) >= 2) {
                $first = '%'.addcslashes($parts[0], '%_\\').'%';
                $last = '%'.addcslashes(implode(' ', array_slice($parts, 1)), '%_\\').'%';
                $dq->orWhere(function (Builder $pair) use ($first, $last) {
                    $pair->where('first_name', 'like', $first)
                        ->where('last_name', 'like', $last);
                });
            }
        });
    }
}
