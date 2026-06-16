import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

/**
 * Forms with data-swal-confirm show a themed modal instead of window.confirm().
 * Optional: data-swal-title, data-swal-confirm-text, data-swal-cancel-text
 */
document.addEventListener(
    'submit',
    async (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }
        const text = form.dataset.swalConfirm;
        if (!text) {
            return;
        }

        e.preventDefault();
        e.stopImmediatePropagation();

        const isDark = document.documentElement.classList.contains('dark-theme');
        const title = form.dataset.swalTitle || 'Please confirm';
        const confirmBtn = form.dataset.swalConfirmText || 'Yes, continue';
        const cancelBtn = form.dataset.swalCancelText || 'Cancel';

        const result = await Swal.fire({
            title,
            text,
            icon: 'warning',
            showCancelButton: true,
            focusCancel: true,
            confirmButtonColor: '#283b69',
            cancelButtonColor: '#64748b',
            confirmButtonText: confirmBtn,
            cancelButtonText: cancelBtn,
            reverseButtons: true,
            background: isDark ? '#1e293b' : '#ffffff',
            color: isDark ? '#f1f5f9' : '#0f172a',
        });

        if (result.isConfirmed) {
            window.heroPageLoader?.show?.();
            form.submit();
        }
    },
    true,
);

window.Swal = Swal;
