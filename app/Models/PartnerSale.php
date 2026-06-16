<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'membership_id',
        'plan_id',
        'sale_amount',
        'commission_percent',
        'commission_amount',
        'sold_at',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
        'sale_amount' => 'decimal:2',
        'commission_percent' => 'decimal:2',
        'commission_amount' => 'decimal:2',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
