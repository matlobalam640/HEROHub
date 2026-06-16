<?php

namespace App\Models;

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
}
