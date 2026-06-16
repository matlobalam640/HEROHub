<?php

namespace App\Http\Controllers\Business\Concerns;

use App\Models\Company;
use App\Models\Membership;
use Illuminate\Http\Request;

trait ResolvesBusinessCompany
{
    protected function ownedCompanies(Request $request)
    {
        return Company::query()
            ->where('owner_user_id', $request->user()->id)
            ->orderBy('name')
            ->get();
    }

    protected function currentCompany(Request $request): ?Company
    {
        $companies = $this->ownedCompanies($request);
        if ($companies->isEmpty()) {
            return null;
        }

        $id = (int) $request->session()->get('business_company_id', 0);
        if ($id) {
            $match = $companies->firstWhere('id', $id);
            if ($match) {
                return $match;
            }
        }

        return $companies->first();
    }

    protected function findOwnedCompany(Request $request, int $companyId): Company
    {
        $company = Company::query()
            ->where('owner_user_id', $request->user()->id)
            ->where('id', $companyId)
            ->firstOrFail();

        return $company;
    }

    protected function authorizeMembershipForCompany(Request $request, Membership $membership, Company $company): void
    {
        abort_unless($membership->company_id === $company->id, 404);
    }
}
