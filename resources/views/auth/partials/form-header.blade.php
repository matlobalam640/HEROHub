@props(['badge' => null, 'title', 'description' => null])

<div class="mb-8">
    @if ($badge)
        <div class="hero-auth-eyebrow">
            <span class="inline-flex h-2 w-2 rounded-full bg-[color:var(--hero-primary)]"></span>
            {{ $badge }}
        </div>
    @endif
    <h1 class="hero-auth-title">{{ $title }}</h1>
    @if ($description)
        <p class="hero-auth-lead">{{ $description }}</p>
    @endif
</div>
