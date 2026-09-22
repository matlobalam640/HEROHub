<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Business\Concerns\ResolvesBusinessCompany;
use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Plan;
use App\Services\CompanyBillingService;
use App\Services\CompanyEmployeeCsvImportService;
use App\Support\MembershipNumberGenerator;
use App\Support\PortalInvite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    use ResolvesBusinessCompany;

    public function __construct(
        private CompanyBillingService $billingService,
        private CompanyEmployeeCsvImportService $employeeImportService,
        private MembershipNumberGenerator $membershipNumberGenerator,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $company = $this->currentCompany($request);
        if (! $company) {
            return redirect()->route('business.portal')
                ->withErrors(['company' => 'No company is available. Contact support to link your HR account to an organization.']);
        }

        if ($company->isPendingPayment()) {
            return redirect()->route('business.plan-checkout.show');
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
            ->whereIn('category', ['business', 'corporate'])
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('business.employees.index', [
            'company' => $company,
            'employees' => $employees,
            'plans' => $plans,
            'filter' => $filter,
            'remainingSeats' => $company->remainingSeats(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->currentCompany($request);
        if (! $company) {
            return redirect()->route('business.portal')->withErrors(['company' => 'No company selected.']);
        }

        if ($company->isPendingPayment()) {
            return redirect()->route('business.plan-checkout.show');
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        if (! $company->canAddEmployees(1)) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'Company employee seat limit reached ('.$company->seat_limit.').']);
        }

        try {
            [$user, $userCreated] = $this->employeeImportService->resolveOrCreateEmployeeUser(
                strtolower(trim($validated['email'])),
                $validated['first_name'],
                $validated['last_name'],
            );
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

        $membership = Membership::create([
            'membership_number' => $this->membershipNumberGenerator->nextImportNumber(),
            'plan_id' => (int) $validated['plan_id'],
            'account_user_id' => $user->id,
            'company_id' => $company->id,
            'partner_id' => null,
            'coverage_starts_on' => now(),
            'coverage_ends_on' => now()->addYear(),
            'auto_renew' => true,
            'status' => 'active',
            'billing_provider' => 'company',
        ]);

        Member::create([
            'membership_id' => $membership->id,
            'is_primary' => true,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'date_of_birth' => $validated['date_of_birth'],
            'phone' => $validated['phone'] ?? null,
            'email' => strtolower(trim($validated['email'])),
            'qr_token' => (string) Str::uuid(),
        ]);

        if ($userCreated) {
            PortalInvite::sendUserInvite(
                user: $user,
                subject: 'Your HERO member portal invitation',
                headline: 'You have been enrolled by '.$company->name.'.',
                detailLines: [
                    'Company: '.$company->name,
                    'Membership #: '.$membership->membership_number,
                    'Create your password to open your member portal and view coverage.',
                ],
                actionUrl: PortalInvite::passwordSetupUrl($user),
                actionLabel: 'Create your portal password',
            );
        }

        $this->billingService->recalculate($company);

        return redirect()->route('business.employees.index')->with(
            'status',
            $userCreated ? 'Employee added and portal invite sent.' : 'Employee added and linked to an existing portal account.'
        );
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

    public function importTemplate(): Response
    {
        abort_unless(auth()->user()?->hasRole('business'), 403);

        $filename = 'herohub-employee-import-template.csv';

        return response($this->employeeImportService->sampleCsvContents(), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $company = $this->currentCompany($request);
        if (! $company) {
            return redirect()->route('business.portal')->withErrors(['company' => 'No company selected.']);
        }

        if ($company->isPendingPayment()) {
            return redirect()->route('business.plan-checkout.show');
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $result = $this->employeeImportService->importForCompany(
            $company,
            (string) $request->file('file')->getRealPath()
        );

        if ($result['added'] === 0 && $result['skipped'] === 0 && $result['messages'] !== []) {
            return back()->withErrors(['file' => implode(' ', $result['messages'])]);
        }

        $status = "Import finished: {$result['added']} employees added, {$result['invited']} invites sent, {$result['skipped']} rows skipped.";

        return redirect()
            ->route('business.employees.index')
            ->with('status', $status)
            ->with('employee_import_messages', $result['messages']);
    }
}
