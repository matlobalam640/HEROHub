<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipPlanChangeStripeSession extends Model
{
    protected $fillable = [
        'membership_id',
        'plan_id',
        'interval',
        'stripe_checkout_session_id',
        'amount_total_cents',
        'applied_at',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
    ];

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
