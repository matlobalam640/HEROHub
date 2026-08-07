import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';

const DEFAULT_OPTIONS = {
    dateFormat: 'Y-m-d',
    altInput: true,
    altFormat: 'M j, Y',
    allowInput: false,
    disableMobile: true,
    monthSelectorType: 'static',
};

function parseDateAttr(value) {
    if (! value) {
        return null;
    }

    if (value === 'today') {
        return new Date();
    }

    const parsed = flatpickr.parseDate(value, 'Y-m-d');

    return parsed ?? value;
}

function buildOptions(input) {
    const options = { ...DEFAULT_OPTIONS };

    if (input.dataset.minDate) {
        options.minDate = parseDateAttr(input.dataset.minDate);
    }

    if (input.dataset.maxDate) {
        options.maxDate = parseDateAttr(input.dataset.maxDate);
    }

    options.onChange = (_selectedDates, dateStr, instance) => {
        instance.input.value = dateStr;
        instance.input.dispatchEvent(new Event('input', { bubbles: true }));
        instance.input.dispatchEvent(new Event('change', { bubbles: true }));
    };

    return options;
}

export function initHeroDatePickers(root = document) {
    root.querySelectorAll('input.hero-date-input').forEach((input) => {
        if (input._flatpickr) {
            return;
        }

        flatpickr(input, buildOptions(input));
    });
}

window.initHeroDatePickers = initHeroDatePickers;

document.addEventListener('DOMContentLoaded', () => initHeroDatePickers());
document.addEventListener('alpine:initialized', () => initHeroDatePickers());
