<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberDependent extends Model
{
    use HasFactory;

    protected $fillable = [
        'membership_id',
        'relationship',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'phone',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }
}
