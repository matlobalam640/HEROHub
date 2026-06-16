<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Membership;
use App\Models\Plan;

class CompanyBillingService
{
    /**
     * Recalculate cached headcount and estimated monthly total from active employee memberships.
     */
    public function recalculate(Company $company): void
    {
        $company->loadMissing('defaultPlan');

        $activeCount = Membership::query()
            ->where('company_id', $company->id)
            ->where('status', 'active')
            ->count();

        $perSeat = $this->perSeatMonthlyAmount($company);

        $total = round($activeCount * $perSeat, 2);

        $company->forceFill([
            'billing_cached_active_employees' => $activeCount,
            'billing_cached_monthly_total' => $total,
            'billing_calculated_at' => now(),
        ])->save();
    }

    public function perSeatMonthlyAmount(Company $company): float
    {
        if ($company->billing_per_employee_override !== null) {
            return (float) $company->billing_per_employee_override;
        }

        $plan = $company->defaultPlan;
        if (! $plan) {
            $plan = Plan::query()
                ->where('active', true)
                ->whereIn('category', ['business', 'corporate'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->first();
        }

        if (! $plan) {
            return 0.0;
        }

        return $this->planMonthlyAmount($plan);
    }

    public function planMonthlyAmount(Plan $plan): float
    {
        if ($plan->price_monthly !== null && (float) $plan->price_monthly > 0) {
            return (float) $plan->price_monthly;
        }

        $price = (float) ($plan->price ?? 0);
        if ($price <= 0) {
            return 0.0;
        }

        $interval = strtolower((string) ($plan->billing_interval ?? 'monthly'));

        return $interval === 'yearly' ? round($price / 12, 2) : $price;
    }
}
