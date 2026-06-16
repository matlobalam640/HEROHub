{{-- Inlined: no Vite required. Active state uses class + aria-current + data-nav-active (Alpine no longer touches link classes). Collapsed rail: .portal-sidebar--collapsed on desktop wrapper. --}}
<style id="hero-portal-sidebar-styles">
    .portal-sidebar,
    .portal-sidebar .sidebar-brand,
    .portal-sidebar .sidebar-footer {
        background-color: #ffffff !important;
    }

    .portal-sidebar {
        box-shadow: 8px 0 24px -22px rgba(15, 23, 42, 0.3);
    }

    .portal-sidebar .sidebar-brand {
        backdrop-filter: saturate(120%);
    }

    .portal-sidebar .sidebar-link {
        gap: 0.875rem;
        transition: transform 180ms cubic-bezier(0.2, 0.7, 0.2, 1), background-color 180ms cubic-bezier(0.2, 0.7, 0.2, 1), color 180ms cubic-bezier(0.2, 0.7, 0.2, 1), box-shadow 180ms cubic-bezier(0.2, 0.7, 0.2, 1);
        transform: translateX(0);
        border: 1px solid transparent;
        background-image: none;
        background-color: transparent;
        color: #475569;
        box-shadow: none;
    }

    .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover {
        transform: translateX(0);
        color: #334155;
        background-color: rgba(148, 163, 184, 0.14);
    }

    .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible {
        transform: translateX(0);
        color: #334155;
        background-color: rgba(148, 163, 184, 0.14);
    }

    .portal-sidebar .sidebar-icon i {
        transition: transform 180ms cubic-bezier(0.2, 0.7, 0.2, 1);
    }

    .portal-sidebar .sidebar-link .sidebar-icon {
        color: #64748b;
    }

    .portal-sidebar .sidebar-link:not(.sidebar-link-active):hover .sidebar-icon i {
        transform: scale(1.06);
    }

    .portal-sidebar .sidebar-nav-stack {
        gap: 0.625rem;
    }

    .portal-sidebar .sidebar-nav-stack--pill .sidebar-link--pill {
        border: 1px solid rgba(40, 59, 105, 0.14);
        background-image: linear-gradient(180deg, #ffffff 0%, #f4f5f8 100%);
        color: #2a3341;
        box-shadow: 0 1px 0 rgba(255, 255, 255, 0.85) inset, 0 2px 8px -4px rgba(40, 59, 105, 0.08);
    }

    .portal-sidebar .sidebar-nav-stack--pill .sidebar-link--pill .sidebar-icon {
        color: #283b69;
    }

    .portal-sidebar .sidebar-nav-stack--pill .sidebar-link--pill:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover,
    .portal-sidebar .sidebar-nav-stack--pill .sidebar-link--pill:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible {
        color: #283b69;
        background-image: linear-gradient(180deg, #f8f9fb 0%, #eceef3 100%);
    }

    .portal-sidebar .sidebar-nav-stack--pill .sidebar-link--pill:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover .sidebar-icon,
    .portal-sidebar .sidebar-nav-stack--pill .sidebar-link--pill:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible .sidebar-icon {
        color: #283b69;
    }

    .portal-sidebar .sidebar-nav-stack > .sidebar-menu-item.sidebar-plans-group {
        margin-bottom: 0.125rem;
    }

    .portal-sidebar .sidebar-nav-stack > .sidebar-menu-item.sidebar-plans-group + .sidebar-menu-item {
        margin-top: 0.5rem;
        padding-top: 0.75rem;
        border-top: 1px solid var(--sidebar-border, #eef1f5);
    }

    @media (min-width: 1024px) {
        .portal-sidebar.portal-sidebar--collapsed .sidebar-brand {
            justify-content: center;
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }

        .portal-sidebar.portal-sidebar--collapsed .sidebar-brand img {
            height: 2rem;
            max-width: 4.5rem;
        }

        .portal-sidebar.portal-sidebar--collapsed .sidebar-link {
            justify-content: center !important;
            gap: 0 !important;
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
        }

        .portal-sidebar.portal-sidebar--collapsed .sidebar-plans-nest {
            margin-left: 0 !important;
            border-left-width: 0 !important;
            padding-left: 0 !important;
        }
    }

    /* Overview pills — navy active */
    .portal-sidebar a.sidebar-link--pill.sidebar-link-active,
    .portal-sidebar a.sidebar-link--pill[aria-current="page"],
    .portal-sidebar a.sidebar-link--pill[data-nav-active] {
        background-color: #283b69 !important;
        background-image: none !important;
        border-color: transparent !important;
        color: #ffffff !important;
        font-weight: 600;
        box-shadow: 0 8px 22px rgba(40, 59, 105, 0.38), 0 1px 0 rgba(255, 255, 255, 0.1) inset;
        transform: translateX(0);
    }

    .portal-sidebar a.sidebar-link--pill.sidebar-link-active:hover,
    .portal-sidebar a.sidebar-link--pill[aria-current="page"]:hover,
    .portal-sidebar a.sidebar-link--pill[data-nav-active]:hover {
        background-color: #1f2d52 !important;
        background-image: none !important;
        color: #ffffff !important;
    }

    .portal-sidebar a.sidebar-link--pill.sidebar-link-active .sidebar-icon,
    .portal-sidebar a.sidebar-link--pill.sidebar-link-active:hover .sidebar-icon,
    .portal-sidebar a.sidebar-link--pill[aria-current="page"] .sidebar-icon,
    .portal-sidebar a.sidebar-link--pill[aria-current="page"]:hover .sidebar-icon,
    .portal-sidebar a.sidebar-link--pill[data-nav-active] .sidebar-icon,
    .portal-sidebar a.sidebar-link--pill[data-nav-active]:hover .sidebar-icon {
        color: #ffffff !important;
        opacity: 1 !important;
    }

    .portal-sidebar a.sidebar-link--pill.sidebar-link-active .sidebar-icon i,
    .portal-sidebar a.sidebar-link--pill[aria-current="page"] .sidebar-icon i,
    .portal-sidebar a.sidebar-link--pill[data-nav-active] .sidebar-icon i {
        color: inherit !important;
        opacity: 1 !important;
    }

    /* Standard rows + nested Plans links */
    .portal-sidebar a.sidebar-link:not(.sidebar-link--pill).sidebar-link-active,
    .portal-sidebar a.sidebar-link:not(.sidebar-link--pill)[aria-current="page"],
    .portal-sidebar a.sidebar-link:not(.sidebar-link--pill)[data-nav-active] {
        background-color: #283b69 !important;
        background-image: none !important;
        color: #ffffff !important;
        font-weight: 600;
        box-shadow: 0 8px 18px -10px rgba(40, 59, 105, 0.55);
        transform: translateX(0);
    }

    .portal-sidebar a.sidebar-link:not(.sidebar-link--pill).sidebar-link-active:hover,
    .portal-sidebar a.sidebar-link:not(.sidebar-link--pill)[aria-current="page"]:hover,
    .portal-sidebar a.sidebar-link:not(.sidebar-link--pill)[data-nav-active]:hover {
        background-color: #1f2d52 !important;
        background-image: none !important;
        color: #ffffff !important;
    }

    .portal-sidebar a.sidebar-link:not(.sidebar-link--pill).sidebar-link-active .sidebar-icon,
    .portal-sidebar a.sidebar-link:not(.sidebar-link--pill).sidebar-link-active:hover .sidebar-icon,
    .portal-sidebar a.sidebar-link:not(.sidebar-link--pill)[aria-current="page"] .sidebar-icon,
    .portal-sidebar a.sidebar-link:not(.sidebar-link--pill)[aria-current="page"]:hover .sidebar-icon,
    .portal-sidebar a.sidebar-link:not(.sidebar-link--pill)[data-nav-active] .sidebar-icon,
    .portal-sidebar a.sidebar-link:not(.sidebar-link--pill)[data-nav-active]:hover .sidebar-icon {
        color: #ffffff !important;
        opacity: 1 !important;
    }

    .portal-sidebar a.sidebar-link:not(.sidebar-link--pill).sidebar-link-active .sidebar-icon i,
    .portal-sidebar a.sidebar-link:not(.sidebar-link--pill)[aria-current="page"] .sidebar-icon i,
    .portal-sidebar a.sidebar-link:not(.sidebar-link--pill)[data-nav-active] .sidebar-icon i {
        color: inherit !important;
        opacity: 1 !important;
    }

    .portal-sidebar .sidebar-plans-group .sidebar-section-label {
        position: relative;
    }

    .portal-sidebar .sidebar-plans-group .sidebar-section-label::after {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0.125rem;
        height: 1px;
        background: linear-gradient(90deg, rgba(40, 59, 105, 0.22), rgba(40, 59, 105, 0));
    }

    .portal-sidebar .sidebar-plans-nest {
        margin-left: 0 !important;
        padding-left: 0 !important;
        border-left: 0 !important;
    }

    .portal-sidebar .sidebar-footer a:hover {
        color: var(--sidebar-text-strong) !important;
    }

    .portal-sidebar .sidebar-menu {
        scrollbar-width: thin;
        scrollbar-color: #cfd7e2 transparent;
    }

    .portal-sidebar .sidebar-menu::-webkit-scrollbar {
        width: 8px;
    }

    .portal-sidebar .sidebar-menu::-webkit-scrollbar-thumb {
        background: #cfd7e2;
        border-radius: 999px;
    }

    .portal-sidebar .sidebar-menu::-webkit-scrollbar-thumb:hover {
        background: #b9c5d4;
    }

    .dashboard-card-header,
    .hero-panel-header {
        border-bottom: 1px solid rgba(40, 59, 105, 0.1);
        background-image: linear-gradient(180deg, #ffffff 0%, #f4f5f8 100%);
        box-shadow: 0 1px 0 rgba(255, 255, 255, 0.9) inset;
    }

    .dashboard-card-header__title {
        color: #283b69;
        text-shadow: none;
    }

    .dashboard-card-header__sub,
    .hero-panel-header .text-xs {
        color: rgba(42, 51, 65, 0.78);
    }

    .hero-panel-header .text-sm,
    .hero-panel-header .font-semibold,
    .hero-panel-header .text-slate-800,
    .hero-panel-header .text-slate-900 {
        color: #283b69 !important;
        text-shadow: none;
    }

    .hero-panel-header .text-slate-500,
    .hero-panel-header .text-slate-600 {
        color: rgba(42, 51, 65, 0.72) !important;
    }

    /* Light mode: per-item accent icons (inactive links only; active rows stay white-on-navy) */
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]) .sidebar-icon i.fa-house { color: #6366f1; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]) .sidebar-icon i.fa-chart-column { color: #8b5cf6; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]) .sidebar-icon i.fa-id-card { color: #0ea5e9; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]) .sidebar-icon i.fa-arrow-up-right-dots { color: #ea580c; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]) .sidebar-icon i.fa-people-roof { color: #db2777; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]) .sidebar-icon i.fa-arrows-rotate { color: #0d9488; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]) .sidebar-icon i.fa-user-clock { color: #0891b2; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]) .sidebar-icon i.fa-credit-card { color: #059669; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]) .sidebar-icon i.fa-file-invoice-dollar { color: #16a34a; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]) .sidebar-icon i.fa-gear { color: #7c3aed; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]) .sidebar-icon i.fa-building-user { color: #2563eb; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]) .sidebar-icon i.fa-users { color: #0284c7; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]) .sidebar-icon i.fa-handshake { color: #ca8a04; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]) .sidebar-icon i.fa-user-plus { color: #65a30d; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]) .sidebar-icon i.fa-receipt { color: #c026d3; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]) .sidebar-icon i.fa-chart-pie { color: #9333ea; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]) .sidebar-icon i.fa-clipboard-check { color: #dc2626; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]) .sidebar-icon i.fa-clipboard-list { color: #d97706; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]) .sidebar-icon i.fa-users-gear { color: #1d4ed8; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]) .sidebar-icon i.fa-tags { color: #e11d48; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]) .sidebar-icon i.fa-briefcase { color: #b45309; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]) .sidebar-icon i.fa-city { color: #3b82f6; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]) .sidebar-icon i.fa-building-columns { color: #4f46e5; }

    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover .sidebar-icon i.fa-house,
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible .sidebar-icon i.fa-house { color: #4f46e5; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover .sidebar-icon i.fa-chart-column,
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible .sidebar-icon i.fa-chart-column { color: #7c3aed; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover .sidebar-icon i.fa-id-card,
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible .sidebar-icon i.fa-id-card { color: #0284c7; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover .sidebar-icon i.fa-arrow-up-right-dots,
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible .sidebar-icon i.fa-arrow-up-right-dots { color: #c2410c; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover .sidebar-icon i.fa-people-roof,
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible .sidebar-icon i.fa-people-roof { color: #be185d; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover .sidebar-icon i.fa-arrows-rotate,
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible .sidebar-icon i.fa-arrows-rotate { color: #0f766e; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover .sidebar-icon i.fa-user-clock,
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible .sidebar-icon i.fa-user-clock { color: #0e7490; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover .sidebar-icon i.fa-credit-card,
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible .sidebar-icon i.fa-credit-card { color: #047857; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover .sidebar-icon i.fa-file-invoice-dollar,
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible .sidebar-icon i.fa-file-invoice-dollar { color: #15803d; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover .sidebar-icon i.fa-gear,
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible .sidebar-icon i.fa-gear { color: #6d28d9; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover .sidebar-icon i.fa-building-user,
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible .sidebar-icon i.fa-building-user { color: #1d4ed8; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover .sidebar-icon i.fa-users,
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible .sidebar-icon i.fa-users { color: #0369a1; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover .sidebar-icon i.fa-handshake,
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible .sidebar-icon i.fa-handshake { color: #a16207; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover .sidebar-icon i.fa-user-plus,
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible .sidebar-icon i.fa-user-plus { color: #4d7c0f; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover .sidebar-icon i.fa-receipt,
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible .sidebar-icon i.fa-receipt { color: #a21caf; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover .sidebar-icon i.fa-chart-pie,
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible .sidebar-icon i.fa-chart-pie { color: #7e22ce; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover .sidebar-icon i.fa-clipboard-check,
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible .sidebar-icon i.fa-clipboard-check { color: #b91c1c; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover .sidebar-icon i.fa-clipboard-list,
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible .sidebar-icon i.fa-clipboard-list { color: #b45309; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover .sidebar-icon i.fa-users-gear,
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible .sidebar-icon i.fa-users-gear { color: #1e40af; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover .sidebar-icon i.fa-tags,
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible .sidebar-icon i.fa-tags { color: #be123c; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover .sidebar-icon i.fa-briefcase,
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible .sidebar-icon i.fa-briefcase { color: #92400e; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover .sidebar-icon i.fa-city,
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible .sidebar-icon i.fa-city { color: #2563eb; }
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover .sidebar-icon i.fa-building-columns,
    html:not(.dark-theme) .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible .sidebar-icon i.fa-building-columns { color: #4338ca; }

    /* Vuexy dark-layout — sidebar menu (html.dark-theme from portal) */
    html.dark-theme .portal-sidebar {
        box-shadow: 8px 0 32px -20px rgba(0, 0, 0, 0.55);
    }

    html.dark-theme .portal-sidebar .sidebar-link {
        color: #b4b7bd;
    }

    html.dark-theme .portal-sidebar .sidebar-link .sidebar-icon {
        color: #8c8fa3;
    }

    html.dark-theme .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover,
    html.dark-theme .portal-sidebar .sidebar-link:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible {
        color: #e7eaf0;
        background-color: rgba(115, 103, 240, 0.12);
    }

    html.dark-theme .portal-sidebar .sidebar-section-label {
        color: #7c7f8e !important;
    }

    html.dark-theme .portal-sidebar .sidebar-plans-group .sidebar-section-label::after {
        background: linear-gradient(90deg, rgba(115, 103, 240, 0.35), transparent);
    }

    html.dark-theme .portal-sidebar .sidebar-nav-stack > .sidebar-menu-item.sidebar-plans-group + .sidebar-menu-item {
        border-top-color: #3b4253 !important;
    }

    html.dark-theme .portal-sidebar .sidebar-nav-stack--pill .sidebar-link--pill {
        border: 1px solid #3b4253;
        background-image: linear-gradient(180deg, #343752 0%, #2f3349 100%);
        color: #d0d2d6;
        box-shadow: 0 1px 0 rgba(255, 255, 255, 0.04) inset, 0 4px 14px -8px rgba(0, 0, 0, 0.4);
    }

    html.dark-theme .portal-sidebar .sidebar-nav-stack--pill .sidebar-link--pill .sidebar-icon {
        color: #a59cec;
    }

    html.dark-theme .portal-sidebar .sidebar-nav-stack--pill .sidebar-link--pill:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover,
    html.dark-theme .portal-sidebar .sidebar-nav-stack--pill .sidebar-link--pill:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible {
        color: #e7eaf0;
        background-image: linear-gradient(180deg, #3d425c 0%, #363a52 100%);
        border-color: #4a5168;
    }

    html.dark-theme .portal-sidebar .sidebar-nav-stack--pill .sidebar-link--pill:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):hover .sidebar-icon,
    html.dark-theme .portal-sidebar .sidebar-nav-stack--pill .sidebar-link--pill:not(.sidebar-link-active):not([aria-current="page"]):not([data-nav-active]):focus-visible .sidebar-icon {
        color: #c3b6ff;
    }
</style>
