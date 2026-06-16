<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Business\Concerns\ResolvesBusinessCompany;
use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Plan;
use App\Services\CompanyBillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    use ResolvesBusinessCompany;

    public function __construct(
        private CompanyBillingService $billingService
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $company = $this->currentCompany($request);
        if (! $company) {
            return redirect()->route('business.portal')
                ->withErrors(['company' => 'No company is available. Contact support to link your HR account to an organization.']);
        }

        $coverage = $request->query('coverage');
        $filter = match ($coverage) {
            'active', 'inactive' => $coverage,
            default => 'all',
        };
        $query = Membership::query()
            ->with(['plan', 'members', 'dependents'])
            ->where('company_id', $company->id)
            ->orderByDesc('id');

        if ($filter === 'active') {
            $query->where('status', 'active');
        } elseif ($filter === 'inactive') {
            $query->whereIn('status', ['inactive', 'expired', 'cancelled']);
        }

        $employees = $query->get();

        $plans = Plan::query()
            ->where('active', true)
            ->whereIn('category', ['business', 'corporate', 'retail'])
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('business.employees.index', [
            'company' => $company,
            'employees' => $employees,
            'plans' => $plans,
            'filter' => $filter,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->currentCompany($request);
        if (! $company) {
            return redirect()->route('business.portal')->withErrors(['company' => 'No company selected.']);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $membership = Membership::create([
            'membership_number' => 'HERO-CO-'.strtoupper(Str::random(10)),
            'plan_id' => (int) $validated['plan_id'],
            'account_user_id' => null,
            'company_id' => $company->id,
            'partner_id' => null,
            'coverage_starts_on' => now(),
            'coverage_ends_on' => now()->addYear(),
            'auto_renew' => true,
            'status' => 'active',
        ]);

        Member::create([
            'membership_id' => $membership->id,
            'is_primary' => true,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'qr_token' => (string) Str::uuid(),
        ]);

        $this->billingService->recalculate($company);

        return redirect()->route('business.employees.index')->with('status', 'Employee added.');
    }

    public function destroy(Request $request, Membership $membership): RedirectResponse
    {
        $company = $this->currentCompany($request);
        if (! $company) {
            return redirect()->route('business.portal')->withErrors(['company' => 'No company selected.']);
        }
        $this->authorizeMembershipForCompany($request, $membership, $company);

        $membership->delete();
        $this->billingService->recalculate($company);

        return redirect()->route('business.employees.index')->with('status', 'Employee removed.');
    }

    public function updatePlan(Request $request, Membership $membership): RedirectResponse
    {
        $company = $this->currentCompany($request);
        if (! $company) {
            return redirect()->route('business.portal')->withErrors(['company' => 'No company selected.']);
        }
        $this->authorizeMembershipForCompany($request, $membership, $company);

        $validated = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $membership->update(['plan_id' => (int) $validated['plan_id']]);
        $this->billingService->recalculate($company);

        return back()->with('status', 'Plan updated.');
    }

    public function updateStatus(Request $request, Membership $membership): RedirectResponse
    {
        $company = $this->currentCompany($request);
        if (! $company) {
            return redirect()->route('business.portal')->withErrors(['company' => 'No company selected.']);
        }
        $this->authorizeMembershipForCompany($request, $membership, $company);

        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive,expired,cancelled'],
        ]);

        $membership->update(['status' => $validated['status']]);
        $this->billingService->recalculate($company);

        return back()->with('status', 'Coverage status updated.');
    }

    public function import(Request $request): RedirectResponse
    {
        $company = $this->currentCompany($request);
        if (! $company) {
            return redirect()->route('business.portal')->withErrors(['company' => 'No company selected.']);
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $defaultPlanId = $company->default_plan_id;
        abort_unless($defaultPlanId, 422, 'Set a default enrollment plan under Company billing before importing.');

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return back()->withErrors(['file' => 'Could not read the file.']);
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);

            return back()->withErrors(['file' => 'The CSV is empty.']);
        }

        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header);
        $map = array_flip($header);
        foreach (['first_name', 'last_name'] as $required) {
            if (! isset($map[$required])) {
                fclose($handle);

                return back()->withErrors(['file' => "CSV must include a \"{$required}\" column."]);
            }
        }

        $added = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $first = trim((string) ($row[$map['first_name']] ?? ''));
            $last = trim((string) ($row[$map['last_name']] ?? ''));
            if ($first === '' || $last === '') {
                $skipped++;

                continue;
            }

            $email = isset($map['email']) ? trim((string) ($row[$map['email']] ?? '')) : '';
            $phone = isset($map['phone']) ? trim((string) ($row[$map['phone']] ?? '')) : '';
            $planId = $defaultPlanId;
            if (isset($map['plan_id']) && $row[$map['plan_id']] !== '' && $row[$map['plan_id']] !== null) {
                $planId = (int) $row[$map['plan_id']];
            } elseif (isset($map['plan_code']) && trim((string) ($row[$map['plan_code']] ?? '')) !== '') {
                $code = trim((string) $row[$map['plan_code']]);
                $p = Plan::query()->where('code', $code)->first();
                if ($p) {
                    $planId = $p->id;
                }
            }

            if (! Plan::query()->whereKey($planId)->exists()) {
                $skipped++;

                continue;
            }

            $membership = Membership::create([
                'membership_number' => 'HERO-CO-'.strtoupper(Str::random(10)),
                'plan_id' => $planId,
                'account_user_id' => null,
                'company_id' => $company->id,
                'partner_id' => null,
                'coverage_starts_on' => now(),
                'coverage_ends_on' => now()->addYear(),
                'auto_renew' => true,
                'status' => 'active',
            ]);

            Member::create([
                'membership_id' => $membership->id,
                'is_primary' => true,
                'first_name' => $first,
                'last_name' => $last,
                'phone' => $phone !== '' ? $phone : null,
                'email' => $email !== '' ? $email : null,
                'qr_token' => (string) Str::uuid(),
            ]);

            $added++;
        }

        fclose($handle);

        $this->billingService->recalculate($company);

        return redirect()->route('business.employees.index')->with('status', "Import finished: {$added} employees added, {$skipped} rows skipped.");
    }
}
