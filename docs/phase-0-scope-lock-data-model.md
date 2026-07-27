# Phase 0 Scope Lock - Final Data Model (v1)

This document locks the baseline data model for the Zoho integration workstream.

## 1) User (Portal Account Holder)

### Locked fields (existing)
- `id` (PK)
- `name` (string, required)
- `email` (string, unique, required)
- `email_verified_at` (timestamp, nullable)
- `password` (string, required)
- `remember_token` (nullable)
- `created_at`, `updated_at`

### Locked behavior
- Roles are managed via Spatie role tables (not inline columns).
- Subscription-created users are assigned `customer` role.
- Admin notifications must target users with `admin` role only (no fallback address).

### Notes
- Keep one portal account per email.
- User record is identity/auth entity; membership-specific profile stays on `members`.

## 2) Membership (Subscription Container)

### Locked fields (existing)
- `id` (PK)
- `membership_number` (unique, required)
- `plan_id` (FK -> `plans.id`, required)
- `account_user_id` (FK -> `users.id`, nullable)
- `company_id` (FK -> `companies.id`, nullable)
- `partner_id` (FK -> `partners.id`, nullable)
- `coverage_starts_on` (date, nullable)
- `coverage_ends_on` (date, nullable)
- `auto_renew` (bool, default true)
- `status` (string; expected: `inactive|active|expired|cancelled`)
- `billing_provider` (string; expected: `zoho|stripe|manual`, nullable)
- `billing_customer_id` (string, nullable)
- `billing_subscription_id` (string, unique, nullable)
- `billing_subscription_created_at` (timestamp, nullable)
- `billing_next_billing_at` (date, nullable)
- `billing_last_billing_at` (date, nullable)
- `billing_auto_collect` (bool, nullable)
- `created_at`, `updated_at`

### Locked behavior
- `billing_subscription_id` is the idempotency anchor for Zoho subscription events.
- Webhooks must upsert by `billing_subscription_id`.
- Renewal reminders should use `billing_next_billing_at` as the schedule anchor.

## 3) Primary Member Profile (`members` with `is_primary = true`)

### Locked fields (existing)
- `id` (PK)
- `membership_id` (FK -> `memberships.id`, required)
- `is_primary` (bool, default false)
- `first_name` (string, required)
- `last_name` (string, required)
- `date_of_birth` (date, nullable)
- `gender` (string, nullable)
- `phone` (string, nullable)
- `email` (string, nullable)
- `id_number` (string, nullable)
- `country` (string, nullable)
- `city` (string, nullable)
- `qr_token` (string, unique, required)
- `created_at`, `updated_at`

### Locked behavior
- Exactly one primary member per membership (application invariant).
- Zoho coverage/profile APIs will upsert this row for identity/profile updates.

## 4) Dependents / Coverage Profile (`member_dependents`)

### Locked fields (existing)
- `id` (PK)
- `membership_id` (FK -> `memberships.id`, required)
- `relationship` (string, nullable; e.g. spouse/child/visitor)
- `first_name` (string, required)
- `last_name` (string, required)
- `date_of_birth` (date, nullable)
- `gender` (string, nullable)
- `phone` (string, nullable)
- `created_at`, `updated_at`

### Locked behavior
- Used for both family dependents and temporary visitors.
- Coverage webhook will upsert dependents per membership.

## Phase 0 Additions Required Before Coverage API Mapping

To safely support richer Zoho CRM form payloads, add these columns to `member_dependents`:
- `email` (nullable string)
- `id_number` (nullable string)
- `country` (nullable string)
- `city` (nullable string)

Optional but recommended:
- `external_source_id` (nullable string, indexed) for dependable upsert from Zoho row IDs.

## Event-Driven Webhook Split (Locked)

Use separate endpoints:
- `/api/v1/webhooks/zoho/subscription-created`
- `/api/v1/webhooks/zoho/subscription-updated`
- `/api/v1/webhooks/zoho/subscription-renewed`
- `/api/v1/webhooks/zoho/subscription-cancelled`
- `/api/v1/webhooks/zoho/subscription-deleted`
- `/api/v1/webhooks/zoho/coverage-profile-submitted`

All endpoints must use the same webhook-secret middleware.

## Historical Data Migration Requirement (Locked)

Include a dedicated migration phase to backfill approximately 5 years of Zoho data after final payload shape is approved:
- users
- memberships
- billing identifiers/timeline
- primary member profile
- dependents (where available)

Migration must be idempotent and provide dry-run + reconciliation reporting.
