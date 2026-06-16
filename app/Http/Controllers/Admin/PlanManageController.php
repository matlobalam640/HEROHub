<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlanRequest;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanManageController extends Controller
{
    private const LISTING_KEYS = ['retail', 'small-business', 'corporate'];

    public function create(Request $request): View
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $from = $request->query('from');
        if (! is_string($from) || ! in_array($from, self::LISTING_KEYS, true)) {
            $from = 'retail';
        }

        return view('admin.plans.create', $this->listingViewData($from));
    }

    public function store(StorePlanRequest $request): RedirectResponse
    {
        $features = collect(preg_split('/\r\n|\r|\n/', (string) $request->input('features_text', '')))
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->values()
            ->all();

        Plan::create([
            'code' => $request->input('code'),
            'name' => $request->input('name'),
            'category' => $request->input('category'),
            'tier' => $request->filled('tier') ? $request->input('tier') : null,
            'retail_subgroup' => $request->filled('retail_subgroup') ? $request->input('retail_subgroup') : null,
            'sort_order' => $request->input('sort_order', 0) ?? 0,
            'coverage_days' => $request->filled('coverage_days') ? (int) $request->input('coverage_days') : null,
            'min_members' => $request->filled('min_members') ? (int) $request->input('min_members') : null,
            'max_members' => $request->filled('max_members') ? (int) $request->input('max_members') : null,
            'billing_interval' => $request->filled('billing_interval') ? $request->input('billing_interval') : null,
            'zoho_code_monthly' => $request->filled('zoho_code_monthly') ? trim((string) $request->input('zoho_code_monthly')) : null,
            'zoho_code_yearly' => $request->filled('zoho_code_yearly') ? trim((string) $request->input('zoho_code_yearly')) : null,
            'commitment_months' => $request->filled('commitment_months') ? (int) $request->input('commitment_months') : null,
            'price' => $request->filled('price') ? $request->input('price') : null,
            'price_monthly' => $request->filled('price_monthly') ? $request->input('price_monthly') : null,
            'features' => $features !== [] ? $features : null,
            'ideal_for' => $request->filled('ideal_for') ? $request->input('ideal_for') : null,
            'included_members' => $request->filled('included_members') ? (int) $request->input('included_members') : null,
            'addon_price_yearly' => $request->filled('addon_price_yearly') ? $request->input('addon_price_yearly') : null,
            'currency' => strtoupper($request->input('currency', 'USD')),
            'active' => $request->boolean('active'),
        ]);

        $listing = $request->validated('return_listing');

        $routeName = match ($listing) {
            'small-business' => 'portal.plans.small-business',
            'corporate' => 'portal.plans.corporate',
            default => 'portal.plans.retail',
        };

        return redirect()
            ->route($routeName)
            ->with('status', __('Plan created successfully.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function listingViewData(string $from): array
    {
        return match ($from) {
            'small-business' => [
                'listingFrom' => 'small-business',
                'returnListing' => 'small-business',
                'defaultCategory' => 'business',
                'defaultTier' => null,
                'listingTitle' => 'Small Business Plans',
                'backRoute' => 'portal.plans.small-business',
                'intro' => 'Category defaults to Business for the small business listing. Change any field if needed.',
            ],
            'corporate' => [
                'listingFrom' => 'corporate',
                'returnListing' => 'corporate',
                'defaultCategory' => 'corporate',
                'defaultTier' => null,
                'listingTitle' => 'Corporate Plans',
                'backRoute' => 'portal.plans.corporate',
                'intro' => 'Category defaults to Corporate for the corporate listing. Use tier or name to distinguish offers.',
            ],
            default => [
                'listingFrom' => 'retail',
                'returnListing' => 'retail',
                'defaultCategory' => 'retail',
                'defaultTier' => null,
                'listingTitle' => 'Retail Membership Plans',
                'backRoute' => 'portal.plans.retail',
                'intro' => 'Category defaults to Retail. Set a retail subgroup so the plan appears in the right catalog section.',
            ],
        };
    }
}
