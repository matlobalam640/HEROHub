<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'commission_percent',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'commission_percent' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(PartnerSale::class);
    }
}
