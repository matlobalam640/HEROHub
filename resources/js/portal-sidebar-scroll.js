/**
 * Scroll the portal sidebar menu so the active link (data-nav-active) is visible.
 * scrollIntoView() is unreliable inside fixed + flex + overflow stacks; we set scrollTop on .sidebar-menu.
 */
function isElementDisplayed(el) {
    if (!el) {
        return false;
    }
    return window.getComputedStyle(el).display !== 'none';
}

/**
 * Desktop (lg+): rail is NOT inside the mobile-only wrapper (div.lg:hidden).
 */
function isDesktopRailSidebar(sidebar) {
    return sidebar && !sidebar.closest('div.lg\\:hidden');
}

/**
 * @param {HTMLElement} menu
 * @param {HTMLElement} active
 */
function scrollMenuToRevealActive(menu, active) {
    const linkRect = active.getBoundingClientRect();
    const menuRect = menu.getBoundingClientRect();
    const linkHeight = linkRect.height;

    // Offset of link's top from the top of the menu's visible viewport, plus scrolled distance = position in scrollable content
    const linkTopInScrollableContent = linkRect.top - menuRect.top + menu.scrollTop;
    const targetScrollTop =
        linkTopInScrollableContent - menu.clientHeight / 2 + linkHeight / 2;
    const maxScrollTop = Math.max(0, menu.scrollHeight - menu.clientHeight);

    menu.scrollTop = Math.max(0, Math.min(targetScrollTop, maxScrollTop));
}

export function scrollPortalSidebarActiveIntoView() {
    const wide = window.matchMedia('(min-width: 1024px)').matches;

    for (const sidebar of document.querySelectorAll('.portal-sidebar')) {
        if (wide && !isDesktopRailSidebar(sidebar)) {
            continue;
        }
        if (!wide && isDesktopRailSidebar(sidebar)) {
            continue;
        }

        if (!isElementDisplayed(sidebar)) {
            continue;
        }

        const menu = sidebar.querySelector('.sidebar-menu');
        if (!menu) {
            continue;
        }

        const active = menu.querySelector('a[data-nav-active]');
        if (!active) {
            continue;
        }

        scrollMenuToRevealActive(menu, active);
        return;
    }
}
