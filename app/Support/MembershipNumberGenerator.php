<?php

namespace App\Support;

use App\Models\Membership;

class MembershipNumberGenerator
{
    /**
     * Reserve the next sequential import membership number for the given year.
     * Call once per row during a batch; pass the same generator instance.
     */
    public function nextImportNumber(?int $year = null): string
    {
        $year = $year ?? (int) date('Y');
        $prefix = 'HERO-IMP-'.$year.'-';

        if ($this->lastReservedSequence === null) {
            $latest = Membership::query()
                ->where('membership_number', 'like', $prefix.'%')
                ->orderByDesc('membership_number')
                ->value('membership_number');

            $this->lastReservedSequence = 0;
            if (is_string($latest) && preg_match('/-(\d{6})$/', $latest, $matches)) {
                $this->lastReservedSequence = (int) $matches[1];
            }
        }

        do {
            $this->lastReservedSequence++;
            $candidate = $prefix.str_pad((string) $this->lastReservedSequence, 6, '0', STR_PAD_LEFT);
        } while (Membership::query()->where('membership_number', $candidate)->exists());

        return $candidate;
    }

    public function normalizeProvided(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = strtoupper(trim($value));

        return $normalized !== '' ? $normalized : null;
    }

    private ?int $lastReservedSequence = null;
}
