import { DataTable } from 'simple-datatables';
import 'simple-datatables/dist/style.css';

const baseOptions = {
    searchable: true,
    sortable: true,
    perPage: 10,
    perPageSelect: [5, 10, 25, 50, 100],
    labels: {
        placeholder: 'Search…',
        searchTitle: 'Filter results',
        perPage: 'per page',
        noRows: 'No matching records',
        info: 'Showing {start} to {end} of {rows} entries',
    },
    fixedHeight: false,
};

/**
 * Skip empty tables or colspan “empty state” rows so Simple DataTables does not break.
 */
function shouldSkip(table) {
    if (table.dataset.dtSkip === '1') {
        return true;
    }
    const rows = table.querySelectorAll('tbody tr');
    if (rows.length === 0) {
        return true;
    }
    const first = rows[0].querySelector('td[colspan]');
    return Boolean(first);
}

function initHeroDatatables(root = document) {
    root.querySelectorAll('table.js-datatable').forEach((table) => {
        if (table.dataset.dtInit === '1') {
            return;
        }
        if (shouldSkip(table)) {
            return;
        }
        table.dataset.dtInit = '1';

        let extra = {};
        try {
            if (table.dataset.dtOptions) {
                extra = JSON.parse(table.dataset.dtOptions);
            }
        } catch {
            extra = {};
        }

        const perPage = table.dataset.dtPerPage ? parseInt(table.dataset.dtPerPage, 10) : undefined;

        new DataTable(table, {
            ...baseOptions,
            ...(perPage && !Number.isNaN(perPage) ? { perPage } : {}),
            ...extra,
        });
    });
}

document.addEventListener('DOMContentLoaded', () => initHeroDatatables());
