<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'tier',
        'retail_subgroup',
        'sort_order',
        'coverage_days',
        'min_members',
        'max_members',
        'billing_interval',
        'zoho_code_monthly',
        'zoho_code_yearly',
        'commitment_months',
        'price',
        'price_monthly',
        'features',
        'ideal_for',
        'included_members',
        'addon_price_yearly',
        'currency',
        'active',
    ];

    protected $casts = [
        'features' => 'array',
        'price_monthly' => 'decimal:2',
        'addon_price_yearly' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    /**
     * USD amount for Stripe Checkout (plan change), or null if Stripe cannot charge this interval.
     * Only USD plans are supported for this path.
     */
    public function unitAmountUsdForStripePlanChange(string $interval): ?float
    {
        $interval = strtolower($interval);
        if (! in_array($interval, ['monthly', 'yearly'], true)) {
            return null;
        }

        if (strtoupper((string) ($this->currency ?? 'USD')) !== 'USD') {
            return null;
        }

        if ($interval === 'monthly') {
            $amount = (float) ($this->price ?? 0);
            if ($amount <= 0 && (float) ($this->price_monthly ?? 0) > 0) {
                $amount = (float) $this->price_monthly;
            }

            return $amount > 0 ? round($amount, 2) : null;
        }

        if ($this->billing_interval === 'yearly' && (float) ($this->price ?? 0) > 0) {
            return round((float) $this->price, 2);
        }

        $monthly = (float) ($this->price ?? 0);
        if ($monthly > 0) {
            return round($monthly * 12, 2);
        }

        return null;
    }

    /**
     * Retail (and similar) plans where the member may add household dependents in the customer portal.
     */
    public function allowsFamilyDependents(): bool
    {
        return $this->retail_subgroup === 'annual_family';
    }

    /**
     * Primary list price line for catalog cards (from seeded amounts; Zoho checkout may differ).
     */
    public function catalogPrimaryPriceLine(): string
    {
        if ($this->billing_interval === 'yearly' && $this->price_monthly && (float) $this->price_monthly > 0) {
            return '$'.number_format((float) $this->price, 2).' / year · $'.number_format((float) $this->price_monthly, 2).' / month';
        }

        if ($this->billing_interval === 'one_time' && $this->coverage_days) {
            return '$'.number_format((float) $this->price, 2).' — '.$this->coverage_days.'-day coverage';
        }

        if ($this->billing_interval === 'monthly' && $this->price) {
            return '$'.number_format((float) $this->price, 2).' / month';
        }

        return $this->price ? '$'.number_format((float) $this->price, 2) : '—';
    }

    /**
     * Formatted USD amount for the monthly subscribe button (list pricing from catalog).
     */
    public function checkoutMonthlyButtonPriceLabel(): ?string
    {
        if ((float) ($this->price_monthly ?? 0) > 0) {
            return '$'.number_format((float) $this->price_monthly, 2);
        }

        if ($this->billing_interval === 'monthly' && (float) ($this->price ?? 0) > 0) {
            return '$'.number_format((float) $this->price, 2);
        }

        return null;
    }

    /**
     * Formatted USD amount for the annual subscribe button (list pricing from catalog).
     */
    public function checkoutYearlyButtonPriceLabel(): ?string
    {
        if ($this->billing_interval === 'yearly' && (float) ($this->price ?? 0) > 0) {
            return '$'.number_format((float) $this->price, 2);
        }

        if ($this->billing_interval === 'one_time' && (float) ($this->price ?? 0) > 0) {
            return '$'.number_format((float) $this->price, 2);
        }

        return null;
    }

    /**
     * Retail plans grouped for catalog UI (dashboard / plans page).
     *
     * @return Collection<int, array{key: string, label: string, plans: Collection<int, Plan>}>
     */
    public static function retailCatalogSections(): Collection
    {
        $order = ['10_day', '1_month', 'annual_individual', 'annual_family'];
        $labels = [
            '10_day' => '10-Day Plans',
            '1_month' => '1-Month Plans',
            'annual_individual' => 'Annual — Individual',
            'annual_family' => 'Annual — Family',
        ];

        $grouped = static::query()
            ->where('category', 'retail')
            ->whereNotNull('retail_subgroup')
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get()
            ->groupBy('retail_subgroup');

        return collect($order)
            ->map(fn (string $key) => [
                'key' => $key,
                'label' => $labels[$key] ?? $key,
                'plans' => $grouped->get($key, collect()),
            ])
            ->filter(fn (array $section) => $section['plans']->isNotEmpty())
            ->values();
    }

    /**
     * Small business plans grouped for catalog UI.
     *
     * @return Collection<int, array{key: string, label: string, plans: Collection<int, Plan>}>
     */
    public static function smallBusinessCatalogSections(): Collection
    {
        return static::businessCatalogSectionsForCategory('business');
    }

    /**
     * Corporate plans grouped for catalog UI.
     *
     * @return Collection<int, array{key: string, label: string, plans: Collection<int, Plan>}>
     */
    public static function corporateCatalogSections(): Collection
    {
        return static::businessCatalogSectionsForCategory('corporate');
    }

    /**
     * @return Collection<int, array{key: string, label: string, plans: Collection<int, Plan>}>
     */
    private static function businessCatalogSectionsForCategory(string $category): Collection
    {
        $order = ['workplace', 'manager', 'executive'];
        $labels = [
            'workplace' => 'Workplace Coverage',
            'manager' => 'Manager Plans',
            'executive' => 'Executive Plans',
        ];

        $grouped = static::query()
            ->where('category', $category)
            ->whereNotNull('tier')
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get()
            ->groupBy('tier');

        return collect($order)
            ->map(fn (string $key) => [
                'key' => $key,
                'label' => $labels[$key] ?? ucfirst($key),
                'plans' => $grouped->get($key, collect()),
            ])
            ->filter(fn (array $section) => $section['plans']->isNotEmpty())
            ->values();
    }

    /**
     * Short label for dispatch verification (retail vs business vs corporate).
     */
    public function dispatchMembershipTypeLabel(): string
    {
        return match ($this->category) {
            'retail' => 'Retail',
            'business' => 'Small business',
            'corporate' => 'Corporate',
            default => $this->category ? ucfirst((string) $this->category) : '—',
        };
    }

    /**
     * Human-readable coverage tier / subgroup for dispatch screens.
     */
    public function dispatchCoverageLevelLabel(): string
    {
        if ($this->retail_subgroup) {
            return match ($this->retail_subgroup) {
                '10_day' => '10-day',
                '1_month' => '1-month',
                'annual_individual' => 'Annual — individual',
                'annual_family' => 'Annual — family',
                default => str_replace('_', ' ', (string) $this->retail_subgroup),
            };
        }

        if ($this->tier) {
            return match ($this->tier) {
                'workplace' => 'Workplace',
                'manager' => 'Manager',
                'executive' => 'Executive',
                default => ucfirst(str_replace('_', ' ', (string) $this->tier)),
            };
        }

        return $this->name ?: '—';
    }
}
