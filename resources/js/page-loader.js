/**
 * Full-page loading overlay for in-app navigations, form posts, and axios requests.
 * Opt out: add data-no-page-loader on an <a> or <form>.
 * Manual: window.heroPageLoader.show() / .hide()
 */

const LOADER_ID = 'hero-page-loader';
const STYLES_ID = 'hero-page-loader-keyframes';

/** Hosts that should be treated as the same “local” app when origins differ (localhost vs 127.0.0.1). */
const LOOPBACK_HOSTS = new Set(['localhost', '127.0.0.1', '[::1]']);

let axiosPending = 0;

function injectFallbackKeyframes() {
    if (document.getElementById(STYLES_ID)) {
        return;
    }
    const s = document.createElement('style');
    s.id = STYLES_ID;
    s.textContent = '@keyframes hero-pl-spin{to{transform:rotate(360deg)}}';
    document.head.appendChild(s);
}

function applyOverlaySurface(el) {
    if (document.documentElement.classList.contains('dark-theme')) {
        el.style.background = 'rgba(15, 23, 42, 0.72)';
    } else {
        el.style.background = 'rgba(255, 255, 255, 0.88)';
    }
}

function ensureLoader() {
    let el = document.getElementById(LOADER_ID);
    if (el) {
        return el;
    }
    injectFallbackKeyframes();

    el = document.createElement('div');
    el.id = LOADER_ID;
    el.setAttribute('role', 'status');
    el.setAttribute('aria-live', 'polite');
    el.setAttribute('aria-busy', 'false');
    el.className = 'hero-page-loader';
    el.style.cssText = [
        'position:fixed',
        'inset:0',
        'z-index:2147483647',
        'box-sizing:border-box',
        'display:none',
        'flex-direction:column',
        'align-items:center',
        'justify-content:center',
        'gap:0.75rem',
        'backdrop-filter:blur(4px)',
        '-webkit-backdrop-filter:blur(4px)',
    ].join(';');
    applyOverlaySurface(el);

    el.innerHTML =
        '<div class="hero-page-loader__spinner" aria-hidden="true"></div>' +
        '<span class="hero-page-loader__label">Loading…</span>';

    const spin = el.querySelector('.hero-page-loader__spinner');
    if (spin) {
        spin.style.cssText = [
            'width:3rem',
            'height:3rem',
            'border-radius:9999px',
            'border:4px solid #e2e8f0',
            'border-top-color:#283b69',
            'animation:hero-pl-spin 0.85s linear infinite',
            'flex-shrink:0',
        ].join(';');
    }
    const label = el.querySelector('.hero-page-loader__label');
    if (label) {
        label.style.cssText = 'font-size:0.875rem;font-weight:500;color:#475569;margin:0;font-family:system-ui,sans-serif';
    }

    document.body.appendChild(el);
    return el;
}

function show() {
    const el = ensureLoader();
    applyOverlaySurface(el);
    el.style.display = 'flex';
    el.setAttribute('aria-busy', 'true');
    document.documentElement.setAttribute('data-hero-loading', '');
}

function hide() {
    const el = document.getElementById(LOADER_ID);
    if (el) {
        el.style.display = 'none';
        el.setAttribute('aria-busy', 'false');
    }
    document.documentElement.removeAttribute('data-hero-loading');
}

function isOptedOut(el) {
    return el && el.closest && el.closest('[data-no-page-loader]');
}

function isSameApplicationNav(url) {
    const cur = new URL(window.location.href);
    if (url.protocol !== cur.protocol) {
        return false;
    }
    if (url.origin === cur.origin) {
        return true;
    }
    const uLoop = LOOPBACK_HOSTS.has(url.hostname);
    const cLoop = LOOPBACK_HOSTS.has(cur.hostname);
    return uLoop && cLoop && url.port === cur.port;
}

function shouldHandleLink(anchor, event) {
    if (!(anchor instanceof HTMLAnchorElement) || !anchor.href) {
        return false;
    }
    if (event.defaultPrevented || event.button !== 0) {
        return false;
    }
    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
        return false;
    }
    if (anchor.target === '_blank' || anchor.hasAttribute('download')) {
        return false;
    }
    if (isOptedOut(anchor)) {
        return false;
    }
    const hrefAttr = anchor.getAttribute('href');
    if (hrefAttr === null || hrefAttr === '' || hrefAttr.startsWith('#')) {
        return false;
    }
    let url;
    try {
        url = new URL(anchor.href, window.location.href);
    } catch {
        return false;
    }
    if (!isSameApplicationNav(url)) {
        return false;
    }
    const cur = new URL(window.location.href);
    if (url.pathname === cur.pathname && url.search === cur.search) {
        return false;
    }
    return true;
}

function onDocumentClick(event) {
    const anchor = event.target.closest('a');
    if (!shouldHandleLink(anchor, event)) {
        return;
    }
    show();
    queueMicrotask(() => {
        if (event.defaultPrevented) {
            hide();
        }
    });
}

function onDocumentSubmit(event) {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) {
        return;
    }
    if (form.dataset.noPageLoader !== undefined || isOptedOut(form)) {
        return;
    }
    show();
    queueMicrotask(() => {
        if (event.defaultPrevented) {
            hide();
        }
    });
}

function wireAxios() {
    const axios = window.axios;
    if (!axios || !axios.interceptors) {
        return;
    }
    axios.interceptors.request.use(
        (config) => {
            axiosPending += 1;
            if (axiosPending === 1) {
                show();
            }
            return config;
        },
        (error) => {
            axiosPending = Math.max(0, axiosPending - 1);
            if (axiosPending === 0) {
                hide();
            }
            return Promise.reject(error);
        },
    );
    axios.interceptors.response.use(
        (response) => {
            axiosPending = Math.max(0, axiosPending - 1);
            if (axiosPending === 0) {
                hide();
            }
            return response;
        },
        (error) => {
            axiosPending = Math.max(0, axiosPending - 1);
            if (axiosPending === 0) {
                hide();
            }
            return Promise.reject(error);
        },
    );
}

document.addEventListener('click', onDocumentClick, true);
document.addEventListener('submit', onDocumentSubmit, true);
window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
        hide();
    }
});
window.addEventListener('load', () => hide());

function initPageLoader() {
    hide();
    wireAxios();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPageLoader);
} else {
    initPageLoader();
}

window.heroPageLoader = { show, hide };
