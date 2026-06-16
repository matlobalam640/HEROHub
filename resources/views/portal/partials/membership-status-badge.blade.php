@php
    $st = $status ?? '';
    $badgeClass = match ($st) {
        'active' => 'bg-[color:var(--dashboard-secondary-soft)] text-[color:var(--dashboard-secondary-600)]',
        'expired' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-200',
        'cancelled' => 'bg-rose-100 text-rose-800 dark:bg-rose-950/50 dark:text-rose-200',
        'inactive' => 'bg-slate-100 text-slate-700 dark:bg-slate-700/60 dark:text-slate-200',
        default => 'bg-slate-100 text-slate-600 dark:bg-slate-700/60 dark:text-slate-300',
    };
@endphp
<span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $badgeClass }}">
    {{ $st !== '' ? ucfirst($st) : '—' }}
</span>
