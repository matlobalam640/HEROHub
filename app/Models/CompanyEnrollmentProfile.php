<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyEnrollmentProfile extends Model
{
    protected $fillable = [
        'company_id',
        'business_name',
        'enrollment_kind',
        'contact_first_name',
        'contact_last_name',
        'contact_position',
        'contact_phone',
        'workplace_enrollments',
        'manager_enrollments',
        'executive_enrollments',
        'terms_accepted_at',
        'submitted_at',
        'small_business_submitted_at',
    ];

    protected $casts = [
        'workplace_enrollments' => 'array',
        'manager_enrollments' => 'array',
        'executive_enrollments' => 'array',
        'terms_accepted_at' => 'datetime',
        'submitted_at' => 'datetime',
        'small_business_submitted_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
