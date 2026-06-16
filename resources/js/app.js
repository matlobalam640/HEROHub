import './bootstrap';

import '@fortawesome/fontawesome-free/css/all.min.css';

import './datatables';
import './swal-confirm';
import './page-loader';
import { scrollPortalSidebarActiveIntoView } from './portal-sidebar-scroll';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

Alpine.start();

function scheduleSidebarScroll() {
    scrollPortalSidebarActiveIntoView();
    requestAnimationFrame(() => scrollPortalSidebarActiveIntoView());
    requestAnimationFrame(() => requestAnimationFrame(() => scrollPortalSidebarActiveIntoView()));
}

scheduleSidebarScroll();
window.addEventListener('load', scheduleSidebarScroll, { once: true });

document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === '/') {
        e.preventDefault();
        document.querySelector('header input[type="search"]')?.focus();
    }
});
