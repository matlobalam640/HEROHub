<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Membership extends Model
{
    use HasFactory;

    protected $fillable = [
        'membership_number',
        'plan_id',
        'account_user_id',
        'company_id',
        'partner_id',
        'coverage_starts_on',
        'coverage_ends_on',
        'auto_renew',
        'status',
        'billing_provider',
        'billing_customer_id',
        'billing_subscription_id',
        'billing_subscription_created_at',
        'billing_next_billing_at',
        'billing_last_billing_at',
        'billing_auto_collect',
    ];

    protected $casts = [
        'coverage_starts_on' => 'date',
        'coverage_ends_on' => 'date',
        'auto_renew' => 'boolean',
        'billing_subscription_created_at' => 'datetime',
        'billing_next_billing_at' => 'date',
        'billing_last_billing_at' => 'date',
        'billing_auto_collect' => 'boolean',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function accountUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'account_user_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function primaryMember(): HasOne
    {
        return $this->hasOne(Member::class)->where('is_primary', true);
    }

    public function dependents(): HasMany
    {
        return $this->hasMany(MemberDependent::class);
    }
}
