<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Membership;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ComingSoonController extends Controller
{
    public function __invoke(Request $request, string $page)
    {
        $preview = match ($page) {
            'companies' => [
                'rows' => Company::query()->orderBy('name')->limit(100)->get(),
            ],
            'partners' => [
                'rows' => Partner::query()->orderBy('name')->limit(100)->get(),
            ],
            'memberships' => [
                'rows' => Membership::query()
                    ->with(['plan', 'company', 'accountUser', 'partner', 'primaryMember'])
                    ->orderByDesc('id')
                    ->limit(100)
                    ->get(),
            ],
            'customers' => [
                'rows' => User::role('customer')
                    ->with(['latestMembership.plan'])
                    ->orderBy('name')
                    ->limit(100)
                    ->get(),
            ],
            'reports' => [
                'stats' => [
                    ['label' => 'Active memberships', 'value' => Membership::where('status', 'active')->count()],
                    ['label' => 'Total memberships', 'value' => Membership::count()],
                    ['label' => 'Partner organizations', 'value' => Partner::where('active', true)->count()],
                ],
            ],
            'settings' => [
                'items' => array_merge([
                    ['label' => 'Application name', 'value' => config('app.name')],
                    ['label' => 'Environment', 'value' => app()->environment()],
                    ['label' => 'Timezone', 'value' => config('app.timezone')],
                ], $this->sessionSettingsRows()),
            ],
            default => [],
        };

        if ($page === 'settings' && $request->user()?->hasRole('admin')) {
            $preview['adminUsers'] = User::query()
                ->with('roles')
                ->whereKeyNot($request->user()->id)
                ->orderBy('name')
                ->paginate(25)
                ->withPath(route('portal.coming-soon', ['page' => 'settings']));
        }

        return view('coming-soon', [
            'page' => $page,
            'preview' => $preview,
            'headerMetrics' => $this->headerMetrics($page, $preview),
        ]);
    }

    /**
     * @param  array<string, mixed>  $preview
     * @return list<array{label: string, value: string|int|float}>
     */
    private function headerMetrics(string $page, array $preview): array
    {
        if ($page === 'reports' && isset($preview['stats'])) {
            return array_map(static fn (array $s) => [
                'label' => $s['label'],
                'value' => $s['value'],
            ], $preview['stats']);
        }

        if ($page === 'settings' && isset($preview['items'])) {
            $metrics = [
                ['label' => 'Environment', 'value' => (string) app()->environment()],
                ['label' => 'PHP', 'value' => PHP_VERSION],
            ];
            $sessionCount = $this->countActiveSignedInUsers();
            if ($sessionCount !== null) {
                $metrics[] = ['label' => 'Signed in', 'value' => $sessionCount];
            }

            return $metrics;
        }

        $rowCount = isset($preview['rows']) ? $preview['rows']->count() : 0;

        return match ($page) {
            'memberships' => [
                ['label' => 'Total', 'value' => Membership::count()],
                ['label' => 'Active', 'value' => Membership::where('status', 'active')->count()],
                ['label' => 'In preview', 'value' => $rowCount],
            ],
            'customers' => [
                ['label' => 'Customers', 'value' => User::role('customer')->count()],
                ['label' => 'With membership', 'value' => User::role('customer')->has('memberships')->count()],
                ['label' => 'In preview', 'value' => $rowCount],
            ],
            'companies' => [
                ['label' => 'Companies', 'value' => Company::count()],
                ['label' => 'In preview', 'value' => $rowCount],
            ],
            'partners' => [
                ['label' => 'Partners', 'value' => Partner::count()],
                ['label' => 'Active', 'value' => Partner::where('active', true)->count()],
                ['label' => 'In preview', 'value' => $rowCount],
            ],
            default => [],
        };
    }

    /**
     * @return list<array{label: string, value: string|int|float}>
     */
    private function sessionSettingsRows(): array
    {
        $driver = config('session.driver');
        $count = $this->countActiveSignedInUsers();

        $signedInLabel = $count !== null
            ? (string) $count
            : match ($driver) {
                'database' => '— (sessions table or user_id column missing)',
                'file' => config('session.encrypt')
                    ? '— (turn off session encryption, or use database sessions)'
                    : '— (could not read session files)',
                'redis', 'memcached', 'dynamodb' => '— (use file or database sessions to estimate sign-ins)',
                'cookie', 'array' => '— (sessions not stored on server)',
                default => '—',
            };

        return [
            ['label' => 'Session driver', 'value' => $driver],
            ['label' => 'Users signed in (estimate)', 'value' => $signedInLabel],
        ];
    }

    /**
     * Distinct user IDs with a session last touched within the session lifetime.
     * Database driver: uses sessions.user_id when present.
     * File driver: scans session files for the web guard login key (best-effort).
     */
    private function countActiveSignedInUsers(): ?int
    {
        return match (config('session.driver')) {
            'database' => $this->countActiveSignedInUsersDatabase(),
            'file' => $this->countActiveSignedInUsersFile(),
            default => null,
        };
    }

    private function countActiveSignedInUsersDatabase(): ?int
    {
        $table = config('session.table', 'sessions');

        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'last_activity')) {
            return null;
        }

        if (! Schema::hasColumn($table, 'user_id')) {
            return null;
        }

        $cutoff = time() - ((int) config('session.lifetime', 120) * 60);

        try {
            $row = DB::table($table)
                ->where('last_activity', '>=', $cutoff)
                ->whereNotNull('user_id')
                ->selectRaw('count(distinct user_id) as aggregate')
                ->first();

            return $row ? (int) $row->aggregate : 0;
        } catch (\Throwable) {
            return null;
        }
    }

    private function countActiveSignedInUsersFile(): ?int
    {
        if (config('session.encrypt')) {
            return null;
        }

        $dir = config('session.files');

        if (! is_dir($dir)) {
            return 0;
        }

        $loginKey = Auth::guard(config('auth.defaults.guard', 'web'))->getName();
        $lifetimeSec = (int) config('session.lifetime', 120) * 60;
        $cutoff = time() - $lifetimeSec;
        $userIds = [];

        try {
            foreach (File::files($dir) as $file) {
                if ($file->getMTime() < $cutoff) {
                    continue;
                }

                $raw = @file_get_contents($file->getRealPath());

                if ($raw === false || $raw === '') {
                    continue;
                }

                $data = @unserialize($raw);

                if (! is_array($data) || ! array_key_exists($loginKey, $data)) {
                    continue;
                }

                $id = $data[$loginKey];

                if (is_int($id) || (is_string($id) && ctype_digit($id))) {
                    $userIds[(int) $id] = true;
                }
            }
        } catch (\Throwable) {
            return null;
        }

        return count($userIds);
    }
}
