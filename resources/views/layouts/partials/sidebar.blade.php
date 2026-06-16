@php
    $mobile = $mobile ?? false;
    $isCustomer = auth()->check() && auth()->user()->hasRole('customer');
    $isBusinessNav = auth()->check() && \App\Providers\RouteServiceProvider::isBusinessPortalPrimary(auth()->user());
    $isPartnerNav = auth()->check() && \App\Providers\RouteServiceProvider::isPartnerPortalPrimary(auth()->user());
    $hasPersonalMembership = auth()->check()
        && \App\Models\Membership::query()->where('account_user_id', auth()->id())->exists();
    $showMyMembershipNav = auth()->check() && (
        auth()->user()->hasRole('customer')
        || ($isBusinessNav && $hasPersonalMembership)
    );
    $sidebarHomeUrl = route('dashboard');
    if (auth()->check() && \App\Providers\RouteServiceProvider::isCustomerPortalOnly(auth()->user())) {
        $sidebarHomeUrl = route('customer.membership');
    } elseif ($isBusinessNav) {
        $sidebarHomeUrl = route('business.portal');
    } elseif ($isPartnerNav) {
        $sidebarHomeUrl = route('partner.portal');
    }
    $portalPage = request()->routeIs('portal.coming-soon') ? request()->route('page') : null;
    $planFormFrom = request()->routeIs('admin.plans.create')
        ? (is_string(request()->query('from')) && in_array(request()->query('from'), ['retail', 'small-business', 'corporate'], true)
            ? request()->query('from')
            : 'retail')
        : null;
    $portalPlansRetail = request()->routeIs('portal.plans.retail') || $planFormFrom === 'retail';
    $portalPlansSmallBusiness = request()->routeIs('portal.plans.small-business') || $planFormFrom === 'small-business';
    $portalPlansCorporate = request()->routeIs('portal.plans.corporate') || $planFormFrom === 'corporate';
@endphp

<div class="flex h-full min-h-0 flex-col">
    <a
        href="{{ $sidebarHomeUrl }}"
        class="sidebar-brand flex shrink-0 items-center gap-3 px-5 py-5 outline-none transition-opacity hover:opacity-95 focus-visible:rounded-md focus-visible:ring-2 focus-visible:ring-[color:rgba(40,59,105,0.22)] focus-visible:ring-offset-2 focus-visible:ring-offset-[color:var(--sidebar-surface)]"
    >
        <img
            src="{{ asset('brand/hero-logo.png') }}"
            alt="{{ config('app.name', 'HERO') }}"
            class="h-10 w-auto shrink-0 object-contain object-left transition-all duration-200"
            width="160"
            height="40"
            loading="eager"
            decoding="async"
        />
        <div
            class="flex min-w-0 flex-1 items-center gap-2"
            @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless
        >
            <div class="truncate text-sm font-semibold tracking-tight text-[color:var(--sidebar-text-strong)]">{{ config('app.name', 'HERO') }}</div>
            <i class="fa-solid fa-circle shrink-0 text-[0.35rem] text-[color:var(--sidebar-section)] opacity-70" aria-hidden="true"></i>
        </div>
    </a>

    <div class="px-5" aria-hidden="true">
        <div class="h-px bg-[color:var(--sidebar-border)]"></div>
    </div>

    <nav
        class="sidebar-menu min-h-0 flex-1 overflow-y-auto px-3 py-8 sm:px-4"
        aria-label="{{ __('Main navigation') }}"
    >
        <ul class="sidebar-menu-root" role="list">
            @unless($isCustomer || $isBusinessNav || $isPartnerNav)
            {{-- Overview — Dashboards & Reports as pill CTAs --}}
            <li class="sidebar-nav-group">
                <span
                    class="sidebar-section-label block"
                    @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless
                >Overview</span>
                <ul class="sidebar-nav-stack sidebar-nav-stack--pill" role="list">
                    <li class="sidebar-menu-item">
                        <a href="{{ route('dashboard') }}"
                           class="sidebar-link sidebar-link--pill group {{ request()->routeIs('dashboard') ? 'sidebar-link-active' : '' }}"
                           @if(request()->routeIs('dashboard')) aria-current="page" data-nav-active @endif
                           title="Dashboards">
                            <span class="sidebar-icon shrink-0"><i class="fa-solid fa-house fa-fw" aria-hidden="true"></i></span>
                            <span class="min-w-0 flex-1 truncate" @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless>Dashboards</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ route('portal.coming-soon', ['page' => 'reports']) }}"
                           class="sidebar-link sidebar-link--pill group {{ $portalPage === 'reports' ? 'sidebar-link-active' : '' }}"
                           @if($portalPage === 'reports') aria-current="page" data-nav-active @endif
                           title="Reports">
                            <span class="sidebar-icon shrink-0"><i class="fa-solid fa-chart-column fa-fw" aria-hidden="true"></i></span>
                            <span class="min-w-0 flex-1 truncate" @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless>Reports</span>
                        </a>
                    </li>
                </ul>
            </li>
            @endunless

            {{-- Personal membership (retail customers + company HR when their login has a linked membership) --}}
            @if($showMyMembershipNav)
            <li class="sidebar-nav-group">
                <span
                    class="sidebar-section-label block"
                    @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless
                >My membership</span>
                <ul class="sidebar-nav-stack" role="list">
                    <li class="sidebar-menu-item">
                        <a href="{{ route('customer.membership') }}"
                           class="sidebar-link group {{ request()->routeIs('customer.membership') ? 'sidebar-link-active' : '' }}"
                           @if(request()->routeIs('customer.membership')) aria-current="page" data-nav-active @endif>
                            <span class="sidebar-icon shrink-0"><i class="fa-solid fa-id-card fa-fw" aria-hidden="true"></i></span>
                            <span class="min-w-0 flex-1 truncate" @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless>Membership Details & ID Card</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ route('customer.membership.plan') }}" class="sidebar-link group {{ request()->routeIs('customer.membership.plan') ? 'sidebar-link-active' : '' }}"
                           @if(request()->routeIs('customer.membership.plan')) aria-current="page" data-nav-active @endif>
                            <span class="sidebar-icon shrink-0"><i class="fa-solid fa-arrow-up-right-dots fa-fw" aria-hidden="true"></i></span>
                            <span class="min-w-0 flex-1 truncate" @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless>Upgrade / Downgrade Plan</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ route('customer.membership.family') }}" class="sidebar-link group {{ request()->routeIs('customer.membership.family') ? 'sidebar-link-active' : '' }}"
                           @if(request()->routeIs('customer.membership.family')) aria-current="page" data-nav-active @endif>
                            <span class="sidebar-icon shrink-0"><i class="fa-solid fa-people-roof fa-fw" aria-hidden="true"></i></span>
                            <span class="min-w-0 flex-1 truncate" @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless>Family Members</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ route('customer.membership') }}#auto-renew" class="sidebar-link group">
                            <span class="sidebar-icon shrink-0"><i class="fa-solid fa-arrows-rotate fa-fw" aria-hidden="true"></i></span>
                            <span class="min-w-0 flex-1 truncate" @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless>Auto-Renew</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ route('customer.membership.visitors') }}" class="sidebar-link group {{ request()->routeIs('customer.membership.visitors') ? 'sidebar-link-active' : '' }}"
                           @if(request()->routeIs('customer.membership.visitors')) aria-current="page" data-nav-active @endif>
                            <span class="sidebar-icon shrink-0"><i class="fa-solid fa-user-clock fa-fw" aria-hidden="true"></i></span>
                            <span class="min-w-0 flex-1 truncate" @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless>Visitor Coverage</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ route('customer.membership.billing') }}" class="sidebar-link group {{ request()->routeIs('customer.membership.billing') ? 'sidebar-link-active' : '' }}"
                           @if(request()->routeIs('customer.membership.billing')) aria-current="page" data-nav-active @endif>
                            <span class="sidebar-icon shrink-0"><i class="fa-solid fa-credit-card fa-fw" aria-hidden="true"></i></span>
                            <span class="min-w-0 flex-1 truncate" @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless>Payment Method</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ route('customer.membership.payments') }}" class="sidebar-link group {{ request()->routeIs('customer.membership.payments') ? 'sidebar-link-active' : '' }}"
                           @if(request()->routeIs('customer.membership.payments')) aria-current="page" data-nav-active @endif>
                            <span class="sidebar-icon shrink-0"><i class="fa-solid fa-file-invoice-dollar fa-fw" aria-hidden="true"></i></span>
                            <span class="min-w-0 flex-1 truncate" @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless>Payment History & Invoices</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ route('profile.edit') }}" class="sidebar-link group {{ request()->routeIs('profile.*') ? 'sidebar-link-active' : '' }}"
                           @if(request()->routeIs('profile.*')) aria-current="page" data-nav-active @endif>
                            <span class="sidebar-icon shrink-0"><i class="fa-solid fa-gear fa-fw" aria-hidden="true"></i></span>
                            <span class="min-w-0 flex-1 truncate" @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless>Settings</span>
                        </a>
                    </li>
                </ul>
            </li>
            @endif

            @if($isBusinessNav)
            <li class="sidebar-nav-group">
                <span
                    class="sidebar-section-label block"
                    @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless
                >Company / HR</span>
                <ul class="sidebar-nav-stack" role="list">
                    <li class="sidebar-menu-item">
                        <a href="{{ route('business.portal') }}"
                           class="sidebar-link group {{ request()->routeIs('business.portal') ? 'sidebar-link-active' : '' }}"
                           @if(request()->routeIs('business.portal')) aria-current="page" data-nav-active @endif>
                            <span class="sidebar-icon shrink-0"><i class="fa-solid fa-building-user fa-fw" aria-hidden="true"></i></span>
                            <span class="min-w-0 flex-1 truncate" @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless>Overview</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ route('business.employees.index') }}"
                           class="sidebar-link group {{ request()->routeIs('business.employees.*') || request()->routeIs('business.visitors.*') ? 'sidebar-link-active' : '' }}"
                           @if(request()->routeIs('business.employees.*') || request()->routeIs('business.visitors.*')) aria-current="page" data-nav-active @endif>
                            <span class="sidebar-icon shrink-0"><i class="fa-solid fa-users fa-fw" aria-hidden="true"></i></span>
                            <span class="min-w-0 flex-1 truncate" @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless>Employees &amp; coverage</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ route('business.billing.edit') }}"
                           class="sidebar-link group {{ request()->routeIs('business.billing.*') ? 'sidebar-link-active' : '' }}"
                           @if(request()->routeIs('business.billing.*')) aria-current="page" data-nav-active @endif>
                            <span class="sidebar-icon shrink-0"><i class="fa-solid fa-file-invoice-dollar fa-fw" aria-hidden="true"></i></span>
                            <span class="min-w-0 flex-1 truncate" @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless>Company billing</span>
                        </a>
                    </li>
                </ul>
            </li>
            @endif

            @if($isPartnerNav)
            <li class="sidebar-nav-group">
                <span
                    class="sidebar-section-label block"
                    @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless
                >Partner / Reseller</span>
                <ul class="sidebar-nav-stack" role="list">
                    <li class="sidebar-menu-item">
                        <a href="{{ route('partner.portal') }}"
                           class="sidebar-link group {{ request()->routeIs('partner.portal') ? 'sidebar-link-active' : '' }}"
                           @if(request()->routeIs('partner.portal')) aria-current="page" data-nav-active @endif>
                            <span class="sidebar-icon shrink-0"><i class="fa-solid fa-handshake fa-fw" aria-hidden="true"></i></span>
                            <span class="min-w-0 flex-1 truncate" @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless>Overview</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ route('partner.enroll.create') }}"
                           class="sidebar-link group {{ request()->routeIs('partner.enroll.*') ? 'sidebar-link-active' : '' }}"
                           @if(request()->routeIs('partner.enroll.*')) aria-current="page" data-nav-active @endif>
                            <span class="sidebar-icon shrink-0"><i class="fa-solid fa-user-plus fa-fw" aria-hidden="true"></i></span>
                            <span class="min-w-0 flex-1 truncate" @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless>Enroll member</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ route('partner.sales.index') }}"
                           class="sidebar-link group {{ request()->routeIs('partner.sales.*') ? 'sidebar-link-active' : '' }}"
                           @if(request()->routeIs('partner.sales.*')) aria-current="page" data-nav-active @endif>
                            <span class="sidebar-icon shrink-0"><i class="fa-solid fa-receipt fa-fw" aria-hidden="true"></i></span>
                            <span class="min-w-0 flex-1 truncate" @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless>Sales &amp; status</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ route('partner.commissions') }}"
                           class="sidebar-link group {{ request()->routeIs('partner.commissions') ? 'sidebar-link-active' : '' }}"
                           @if(request()->routeIs('partner.commissions')) aria-current="page" data-nav-active @endif>
                            <span class="sidebar-icon shrink-0"><i class="fa-solid fa-chart-pie fa-fw" aria-hidden="true"></i></span>
                            <span class="min-w-0 flex-1 truncate" @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless>Commissions</span>
                        </a>
                    </li>
                </ul>
            </li>
            @endif

            @unless($isCustomer || $isBusinessNav || $isPartnerNav)
            {{-- Dispatch --}}
            <li class="sidebar-nav-group">
                <span
                    class="sidebar-section-label block"
                    @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless
                >Dispatch</span>
                <ul class="sidebar-nav-stack" role="list">
                    <li class="sidebar-menu-item">
                        <a href="{{ route('dispatch.verification') }}"
                           class="sidebar-link group {{ request()->routeIs('dispatch.verification') ? 'sidebar-link-active' : '' }} {{ ! auth()->user()->hasAnyRole(['dispatch', 'admin']) ? 'sidebar-link-muted pointer-events-none' : '' }}"
                           @if(request()->routeIs('dispatch.verification') && auth()->user()->hasAnyRole(['dispatch', 'admin'])) aria-current="page" data-nav-active @endif
                           title="{{ auth()->user()->hasAnyRole(['dispatch', 'admin']) ? 'Coverage verification' : 'Requires dispatch or admin role' }}">
                            <span class="sidebar-icon shrink-0"><i class="fa-solid fa-clipboard-check fa-fw" aria-hidden="true"></i></span>
                            <span class="min-w-0 flex-1 truncate" @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless>Coverage Verification</span>
                        </a>
                    </li>
                </ul>
            </li>

            {{-- Operations — same pattern --}}
            <li class="sidebar-nav-group">
                <span
                    class="sidebar-section-label block"
                    @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless
                >Operations</span>
                <ul class="sidebar-nav-stack" role="list">
                    <li class="sidebar-menu-item">
                        <a href="{{ route('portal.coming-soon', ['page' => 'memberships']) }}"
                           class="sidebar-link group {{ $portalPage === 'memberships' ? 'sidebar-link-active' : '' }}"
                           @if($portalPage === 'memberships') aria-current="page" data-nav-active @endif>
                            <span class="sidebar-icon shrink-0"><i class="fa-solid fa-clipboard-list fa-fw" aria-hidden="true"></i></span>
                            <span class="min-w-0 flex-1 truncate" @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless>Memberships</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ route('portal.coming-soon', ['page' => 'customers']) }}"
                           class="sidebar-link group {{ $portalPage === 'customers' ? 'sidebar-link-active' : '' }}"
                           @if($portalPage === 'customers') aria-current="page" data-nav-active @endif>
                            <span class="sidebar-icon shrink-0"><i class="fa-solid fa-users-gear fa-fw" aria-hidden="true"></i></span>
                            <span class="min-w-0 flex-1 truncate" @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless>Customers</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item sidebar-plans-group">
                        <div class="space-y-1">
                            <div
                                class="sidebar-section-label block !mt-0.5 !pb-1 !pt-2 !normal-case !tracking-normal !text-[0.7rem] text-[color:var(--sidebar-text-strong)]"
                                @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless
                            >Plans</div>
                            <ul role="list" class="sidebar-plans-nest space-y-1">
                                <li class="sidebar-menu-item">
                                    <a href="{{ route('portal.plans.retail') }}"
                                       class="sidebar-link group text-[0.8125rem] {{ $portalPlansRetail ? 'sidebar-link-active' : '' }}"
                                       @if($portalPlansRetail) aria-current="page" data-nav-active @endif
                                       title="Retail Membership Plans">
                                        <span class="sidebar-icon shrink-0"><i class="fa-solid fa-tags fa-fw" aria-hidden="true"></i></span>
                                        <span class="min-w-0 flex-1 truncate leading-snug" @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless>Retail Membership Plans</span>
                                    </a>
                                </li>
                                <li class="sidebar-menu-item">
                                    <a href="{{ route('portal.plans.small-business') }}"
                                       class="sidebar-link group text-[0.8125rem] {{ $portalPlansSmallBusiness ? 'sidebar-link-active' : '' }}"
                                       @if($portalPlansSmallBusiness) aria-current="page" data-nav-active @endif
                                       title="Small Business Plans">
                                        <span class="sidebar-icon shrink-0"><i class="fa-solid fa-briefcase fa-fw" aria-hidden="true"></i></span>
                                        <span class="min-w-0 flex-1 truncate leading-snug" @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless>Small Business Plans</span>
                                    </a>
                                </li>
                                <li class="sidebar-menu-item">
                                    <a href="{{ route('portal.plans.corporate') }}"
                                       class="sidebar-link group text-[0.8125rem] {{ $portalPlansCorporate ? 'sidebar-link-active' : '' }}"
                                       @if($portalPlansCorporate) aria-current="page" data-nav-active @endif
                                       title="Corporate Plans">
                                        <span class="sidebar-icon shrink-0"><i class="fa-solid fa-city fa-fw" aria-hidden="true"></i></span>
                                        <span class="min-w-0 flex-1 truncate leading-snug" @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless>Corporate Plans</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ route('portal.coming-soon', ['page' => 'companies']) }}"
                           class="sidebar-link group {{ $portalPage === 'companies' ? 'sidebar-link-active' : '' }}"
                           @if($portalPage === 'companies') aria-current="page" data-nav-active @endif>
                            <span class="sidebar-icon shrink-0"><i class="fa-solid fa-building-columns fa-fw" aria-hidden="true"></i></span>
                            <span class="min-w-0 flex-1 truncate" @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless>Companies</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ route('portal.coming-soon', ['page' => 'partners']) }}"
                           class="sidebar-link group {{ $portalPage === 'partners' ? 'sidebar-link-active' : '' }}"
                           @if($portalPage === 'partners') aria-current="page" data-nav-active @endif>
                            <span class="sidebar-icon shrink-0"><i class="fa-solid fa-handshake fa-fw" aria-hidden="true"></i></span>
                            <span class="min-w-0 flex-1 truncate" @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless>Partners</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ route('portal.coming-soon', ['page' => 'settings']) }}"
                           class="sidebar-link group {{ $portalPage === 'settings' ? 'sidebar-link-active' : '' }}"
                           @if($portalPage === 'settings') aria-current="page" data-nav-active @endif>
                            <span class="sidebar-icon shrink-0"><i class="fa-solid fa-gear fa-fw" aria-hidden="true"></i></span>
                            <span class="min-w-0 flex-1 truncate" @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless>Settings</span>
                        </a>
                    </li>
                </ul>
            </li>
            @endunless
        </ul>
    </nav>

    <div
        class="sidebar-footer shrink-0 px-5 py-4 text-xs"
        style="color: var(--sidebar-section);"
        @unless($mobile) x-show="!sidebarCollapsed" x-cloak @endunless
    >
        <div class="flex items-center justify-between">
            <span class="opacity-90">© {{ now()->year }} HERO</span>
            <a class="inline-flex items-center gap-1.5 font-medium transition hover:opacity-100" style="color: var(--sidebar-text);" href="{{ route('profile.edit') }}">
                <i class="fa-solid fa-user text-[0.75rem]" aria-hidden="true"></i>
                Profile
            </a>
        </div>
    </div>
</div>
