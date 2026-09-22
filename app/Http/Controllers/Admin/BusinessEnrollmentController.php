<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BusinessEnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessEnrollmentController extends Controller
{
    public function __construct(
        private readonly BusinessEnrollmentService $enrollmentService,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->hasAnyRole(['admin', 'dispatch']), 403);

        return view('admin.business-enrollment.index', [
            'result' => $request->session()->get('business_enrollment_result'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasAnyRole(['admin', 'dispatch']), 403);

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:160'],
            'billing_email' => ['nullable', 'email', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:30'],
            'company_city' => ['nullable', 'string', 'max:80'],
            'company_country' => ['nullable', 'string', 'max:80'],
            'address_line1' => ['nullable', 'string', 'max:160'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'hr_first_name' => ['required', 'string', 'max:80'],
            'hr_last_name' => ['required', 'string', 'max:80'],
            'hr_email' => ['required', 'email', 'max:255'],
            'hr_phone' => ['nullable', 'string', 'max:30'],
        ]);

        $created = $this->enrollmentService->enrollPendingCompany($validated);

        return redirect()
            ->route('admin.business-enrollment.index')
            ->with('business_enrollment_result', [
                'company_id' => $created['company']->id,
                'company_name' => $created['company']->name,
                'hr_name' => $created['owner']->name,
                'hr_email' => $created['owner']->email,
                'owner_created' => $created['owner_created'],
            ])
            ->with('status', 'Business enrolled. Portal invite sent to HR. Awaiting plan selection and USA Payments checkout.');
    }
}
