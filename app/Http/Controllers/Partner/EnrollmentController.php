<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Partner\Concerns\ResolvesPartner;
use App\Models\Plan;
use App\Services\PartnerEnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    use ResolvesPartner;

    public function __construct(
        private PartnerEnrollmentService $enrollmentService
    ) {}

    public function create(Request $request): View
    {
        $partner = $this->requirePartner($request);

        $plans = Plan::query()
            ->where('category', 'retail')
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('partner.enroll', [
            'partner' => $partner,
            'plans' => $plans,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $partner = $this->requirePartner($request);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
        ]);

        $membership = $this->enrollmentService->enroll($partner, $validated);

        return redirect()
            ->route('partner.sales.index')
            ->with('status', 'Member enrolled. Membership #'.$membership->membership_number);
    }
}
