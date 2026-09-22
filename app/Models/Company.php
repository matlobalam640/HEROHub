<?php

namespace App\Models;

use App\Services\BusinessEnrollmentService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'billing_email',
        'phone',
        'country',
        'city',
        'address_line1',
        'address_line2',
        'postal_code',
        'owner_user_id',
        'enrollment_status',
        'activated_at',
        'seat_limit',
        'billing_provider',
        'billing_subscription_id',
        'subscribed_interval',
        'invited_at',
        'default_plan_id',
        'billing_per_employee_override',
        'billing_cached_active_employees',
        'billing_cached_monthly_total',
        'billing_calculated_at',
    ];

    protected $casts = [
        'billing_per_employee_override' => 'decimal:2',
        'billing_cached_monthly_total' => 'decimal:2',
        'billing_calculated_at' => 'datetime',
        'activated_at' => 'datetime',
        'invited_at' => 'datetime',
        'seat_limit' => 'integer',
    ];

    public function ownerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function defaultPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'default_plan_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function enrollmentProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CompanyEnrollmentProfile::class);
    }

    public function isPendingPayment(): bool
    {
        return ($this->enrollment_status ?? BusinessEnrollmentService::STATUS_ACTIVE)
            === BusinessEnrollmentService::STATUS_PENDING_PAYMENT;
    }

    public function isEnrollmentActive(): bool
    {
        return ($this->enrollment_status ?? BusinessEnrollmentService::STATUS_ACTIVE)
            === BusinessEnrollmentService::STATUS_ACTIVE;
    }

    public function remainingSeats(): ?int
    {
        if ($this->seat_limit === null) {
            return null;
        }

        $used = $this->memberships()
            ->whereIn('status', ['active', 'inactive'])
            ->count();

        return max(0, (int) $this->seat_limit - $used);
    }

    public function canAddEmployees(int $count = 1): bool
    {
        if (! $this->isEnrollmentActive()) {
            return false;
        }

        if ($this->seat_limit === null) {
            return true;
        }

        return $this->remainingSeats() >= $count;
    }
}
