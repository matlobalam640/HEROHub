<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Business\Concerns\ResolvesBusinessCompany;
use App\Http\Controllers\Controller;
use App\Models\MemberDependent;
use App\Models\Membership;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
    use ResolvesBusinessCompany;

    public function store(Request $request, Membership $membership): RedirectResponse
    {
        $company = $this->currentCompany($request);
        if (! $company) {
            return redirect()->route('business.portal')->withErrors(['company' => 'No company selected.']);
        }
        $this->authorizeMembershipForCompany($request, $membership, $company);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $membership->dependents()->create([
            ...$validated,
            'relationship' => 'visitor',
        ]);

        return back()->with('status', 'Temporary visitor added.');
    }

    public function destroy(Request $request, MemberDependent $memberDependent): RedirectResponse
    {
        $company = $this->currentCompany($request);
        if (! $company) {
            return redirect()->route('business.portal')->withErrors(['company' => 'No company selected.']);
        }

        $memberDependent->load('membership');
        abort_unless($memberDependent->membership && $memberDependent->membership->company_id === $company->id, 404);
        abort_unless($memberDependent->relationship === 'visitor', 404);

        $memberDependent->delete();

        return back()->with('status', 'Visitor removed.');
    }
}
