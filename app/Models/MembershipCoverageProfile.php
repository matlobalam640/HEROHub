<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembershipCoverageProfile extends Model
{
    protected $fillable = [
        'membership_id',
        'preferred_coverage_start_date',
        'emergency_contact_first_name',
        'emergency_contact_last_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'emergency_contact_gender',
        'notes',
        'photo_id_path',
        'passport_path',
        'insurance_company',
        'health_plan_provider',
        'health_insurer',
        'insurance_policy_number',
        'insurance_effective_start',
        'insurance_effective_end',
        'insurance_member_id',
        'insurance_policy_holder_name',
        'insurance_policy_holder_relationship',
        'insurance_beneficiary_name',
        'insurance_beneficiary_relationship',
        'insurance_provider_phone',
        'insurance_plan_type',
        'medevac_max_benefit',
        'medevac_policy_number',
        'blood_type',
        'allergies',
        'chronic_conditions',
        'medical_condition_flags',
        'resident_status',
        'measurement_unit',
        'height',
        'weight',
        'occupation',
        'primary_care_provider',
        'health_questionnaire',
        'other_medical_info',
        'mailing_street',
        'mailing_city',
        'mailing_state',
        'mailing_zip_code',
        'mailing_country',
        'passport_issued_by',
        'trip_details',
        'travel_preferences',
        'applicant_signature',
        'signature_date',
        'terms_accepted_at',
    ];

    protected $casts = [
        'preferred_coverage_start_date' => 'date',
        'insurance_effective_start' => 'date',
        'insurance_effective_end' => 'date',
        'medical_condition_flags' => 'array',
        'health_questionnaire' => 'array',
        'trip_details' => 'array',
        'travel_preferences' => 'array',
        'signature_date' => 'date',
        'terms_accepted_at' => 'datetime',
    ];

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }
}
