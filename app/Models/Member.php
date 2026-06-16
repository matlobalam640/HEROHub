<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'membership_id',
        'is_primary',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'phone',
        'email',
        'id_number',
        'country',
        'city',
        'qr_token',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'date_of_birth' => 'date',
    ];

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }
}
