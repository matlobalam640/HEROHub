<?php

namespace App\Http\Controllers\Dispatch;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerificationController extends Controller
{
    /**
     * Live coverage lookup for dispatch: search members, dependents, companies, and membership numbers.
     */
    public function index(Request $request): View
    {
        $q = trim((string) $request->input('q', ''));

        $memberships = null;
        $hasSearched = false;

        if ($q !== '') {
            $hasSearched = true;
            $escaped = '%'.addcslashes($q, '%_\\').'%';

            $memberships = Membership::query()
                ->with(['plan', 'company', 'members', 'dependents'])
                ->where(function (Builder $query) use ($q, $escaped) {
                    $query->where('membership_number', 'like', $escaped)
                        ->orWhereHas('members', fn (Builder $mq) => $this->applyMemberSearch($mq, $q, $escaped))
                        ->orWhereHas('dependents', fn (Builder $dq) => $this->applyDependentSearch($dq, $q, $escaped))
                        ->orWhereHas('company', function (Builder $cq) use ($escaped) {
                            $cq->where('name', 'like', $escaped);
                        });
                })
                ->orderByDesc('id')
                ->paginate(25)
                ->withQueryString();
        }

        return view('dispatch.verification.index', [
            'q' => $q,
            'memberships' => $memberships,
            'hasSearched' => $hasSearched,
        ]);
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
