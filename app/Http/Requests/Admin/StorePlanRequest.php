<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9][A-Za-z0-9\-_]*$/', 'unique:plans,code'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', Rule::in(['retail', 'business', 'corporate'])],
            'tier' => ['nullable', 'string', 'max:64'],
            'retail_subgroup' => ['nullable', 'string', Rule::in(['10_day', '1_month', 'annual_individual', 'annual_family'])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'coverage_days' => ['nullable', 'integer', 'min:0', 'max:36500'],
            'min_members' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'max_members' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'billing_interval' => ['nullable', 'string', Rule::in(['monthly', 'yearly', 'one_time'])],
            'zoho_code_monthly' => ['nullable', 'string', 'max:96'],
            'zoho_code_yearly' => ['nullable', 'string', 'max:96'],
            'commitment_months' => ['nullable', 'integer', 'min:0', 'max:120'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'price_monthly' => ['nullable', 'numeric', 'min:0'],
            'features_text' => ['nullable', 'string', 'max:20000'],
            'ideal_for' => ['nullable', 'string', 'max:500'],
            'included_members' => ['nullable', 'integer', 'min:0', 'max:255'],
            'addon_price_yearly' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'active' => ['sometimes', 'boolean'],
            'return_listing' => ['required', 'string', Rule::in(['retail', 'small-business', 'corporate'])],
        ];
    }

    public function messages(): array
    {
        return [
            'code.regex' => 'Code must start with a letter or number and may only contain letters, numbers, hyphens, and underscores.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'active' => $this->boolean('active'),
        ]);

        foreach (['zoho_code_monthly', 'zoho_code_yearly'] as $key) {
            $value = $this->input($key);
            if ($value === '' || $value === null) {
                $this->merge([$key => null]);
            }
        }
    }
}
