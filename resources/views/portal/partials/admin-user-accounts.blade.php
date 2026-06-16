@props([
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, \App\Models\User> $users */
    'users',
])

@php
    $destroyBase = rtrim(url('/admin/users'), '/');
@endphp

<div
    class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-md shadow-slate-200/50 ring-1 ring-slate-100"
    x-data="{
        open: false,
        deleteId: null,
        deleteLabel: '',
        base: @js($destroyBase),
        openFor(id, label) {
            this.deleteId = id;
            this.deleteLabel = label;
            this.open = true;
            this.$nextTick(() => this.$refs.password?.focus());
        },
        close() { this.open = false; this.deleteId = null; this.deleteLabel = ''; }
    }"
    x-on:keydown.escape.window="close()"
>
    <div class="hero-panel-header border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
        <div class="text-sm font-semibold text-slate-800">{{ __('User accounts') }}</div>
        <div class="text-xs text-slate-500">{{ __('Remove portal logins. Membership rows stay; account holder is cleared where linked.') }}</div>
    </div>

    <div class="hero-datatable overflow-x-auto px-2 pb-2">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50/90 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Name') }}</th>
                    <th class="px-4 py-3">{{ __('Email') }}</th>
                    <th class="px-4 py-3">{{ __('Roles') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($users as $u)
                    <tr class="hover:bg-slate-50/60">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $u->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $u->email }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @forelse($u->roles as $role)
                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700">{{ $role->name }}</span>
                                @empty
                                    <span class="text-xs text-slate-400">—</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button
                                type="button"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-800 transition hover:bg-red-100"
                                x-on:click="openFor({{ $u->id }}, @js($u->email))"
                            >
                                <i class="fa-solid fa-user-minus" aria-hidden="true"></i>
                                {{ __('Delete') }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">{{ __('No other user accounts.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="border-t border-slate-100 px-4 py-3">
            {{ $users->links() }}
        </div>
    @endif

    <div
        x-show="open"
        x-cloak
        class="fixed inset-0 z-[60] flex items-center justify-center px-4 py-8 sm:px-6"
        style="display: none;"
    >
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-[1px]" x-on:click="close()" aria-hidden="true"></div>
        <div
            class="relative z-10 w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl ring-1 ring-slate-200/80"
            role="dialog"
            aria-modal="true"
            x-bind:aria-label="'{{ __('Delete user') }}: ' + (deleteLabel || '')"
        >
            <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[color:var(--dashboard-gold-soft)] px-6 py-4">
                <h2 class="text-base font-semibold text-slate-900">{{ __('Delete user account') }}</h2>
                <p class="mt-1 text-sm text-slate-600">
                    <span>{{ __('You are about to remove the account for') }}</span>
                    <span class="font-medium text-slate-900" x-text="deleteLabel"></span>
                </p>
            </div>
            <form method="POST" class="space-y-4 p-6" x-bind:action="base + '/' + deleteId">
                @csrf
                @method('DELETE')
                <div>
                    <label for="admin-delete-user-password" class="block text-sm font-semibold text-slate-700">{{ __('Your password') }}</label>
                    <input
                        id="admin-delete-user-password"
                        x-ref="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="mt-1.5 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-hero-primary focus:ring-hero-primary"
                    />
                    <p class="mt-2 text-xs text-slate-500">{{ __('Enter your admin password to confirm this action.') }}</p>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>
                <div class="flex flex-wrap justify-end gap-2 pt-1">
                    <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50" x-on:click="close()">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
                        {{ __('Delete account') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
