{{ config('app.name') }} — Your membership is ready
================================

Hello{{ $user->name ? ' '.$user->name : '' }},

Your HERO membership {{ $membership->membership_number }} is linked to this email address@if($planName) for {{ $planName }}@endif.

@if($needsPasswordSetup && $passwordResetUrl)
We created a portal account for you from your subscription. Set your password using this link (one-time):
{{ $passwordResetUrl }}

@elseif($needsPasswordSetup)
We could not include a fresh password link. Use "Forgot password" on the sign-in page with this email.

@else
Sign in with this email and your existing portal password.

@endif
Sign in: {{ route('login') }}

My membership: {{ route('customer.membership') }}

What to do next
---------------
1. My membership — coverage, plan, details.
2. Digital card — download PDF from My membership after sign-in (QR for verification).
3. Household — add dependents if your plan includes family coverage.
4. Billing — Zoho Billing for invoices and payment method; portal for membership and card.

---
{{ config('app.name') }} · {{ config('app.url') }}
You received this because a subscription was linked to your account.
