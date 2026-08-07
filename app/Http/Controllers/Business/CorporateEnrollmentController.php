<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Business\Concerns\HandlesCompanyEnrollmentSubmission;
use App\Http\Controllers\Business\Concerns\ResolvesBusinessCompany;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\CorporateEnrollmentService;
use App\Support\CompanyEnrollmentKind;
use App\Support\CorporateEnrollmentRequirement;
use App\Support\CorporateFormTranslations;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CorporateEnrollmentController extends Controller
{
    use HandlesCompanyEnrollmentSubmission;
    use ResolvesBusinessCompany;

    public function __construct(
        private CorporateEnrollmentService $enrollmentService
    ) {}

    public function show(Request $request): View|RedirectResponse
    {
        $company = $this->currentCompany($request);
        if (! $company) {
            return redirect()->route('business.portal')
                ->withErrors(['company' => 'No company selected.']);
        }

        $profile = CorporateEnrollmentRequirement::profileFor($company);
        $kind = CompanyEnrollmentKind::CORPORATE;
        $plansByTier = Plan::query()
            ->where('category', CompanyEnrollmentKind::planCategory($kind))
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('tier');

        return view('business.corporate-enrollment', [
            'company' => $company,
            'profile' => $profile,
            'plansByTier' => $plansByTier,
            'formTitle' => CorporateFormTranslations::en('form_title'),
            'missingSections' => CorporateEnrollmentRequirement::missingSectionLabels($company, $kind),
            'isComplete' => CorporateEnrollmentRequirement::isComplete($company, $kind),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $company = $this->currentCompany($request);
        if (! $company) {
            return redirect()->route('business.portal')->withErrors(['company' => 'No company selected.']);
        }

        $kind = CompanyEnrollmentKind::CORPORATE;
        $this->filterEnrollmentRequest($request, $kind);

        $validated = $request->validate($this->enrollmentValidationRules($kind));

        $this->enrollmentService->submit($company, $validated, $kind);

        return redirect()
            ->route('business.enrollment')
            ->with('status', CorporateFormTranslations::en('saved_status'));
    }
}
