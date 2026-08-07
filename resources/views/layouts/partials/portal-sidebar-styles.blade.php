{{-- Light sidebar polish — complements app.css portal sidebar rules --}}
<style id="hero-portal-sidebar-styles">
    .portal-sidebar {
        box-shadow: 1px 0 0 rgba(40, 59, 105, 0.08);
    }

    .portal-sidebar .sidebar-brand img {
        filter: none;
    }

    .dashboard-card-header,
    .hero-panel-header {
        border-bottom: 1px solid rgba(40, 59, 105, 0.08);
        background: #ffffff;
    }

    .dashboard-card-header__title {
        color: var(--hero-primary);
    }

    .dashboard-card-header__sub,
    .hero-panel-header .text-xs {
        color: rgba(40, 59, 105, 0.62);
    }

    .hero-panel-header .text-sm,
    .hero-panel-header .font-semibold,
    .hero-panel-header .text-slate-800,
    .hero-panel-header .text-slate-900 {
        color: var(--hero-primary) !important;
    }
</style>
