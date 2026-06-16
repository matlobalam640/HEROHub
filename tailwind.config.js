import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                hero: {
                    /* rgb + <alpha-value> so bg-hero-primary, ring-hero-primary/30, etc. resolve in built CSS */
                    primary: 'rgb(40 59 105 / <alpha-value>)',
                    'primary-hover': 'rgb(31 45 82 / <alpha-value>)',
                    'primary-pressed': 'rgb(22 36 56 / <alpha-value>)',
                    'primary-soft': 'rgb(236 239 246 / <alpha-value>)',
                    secondary: 'rgb(255 255 255 / <alpha-value>)',
                },
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                'hero-card': '0 8px 30px -12px rgba(40, 59, 105, 0.14)',
                'hero-cta': '0 6px 18px -4px rgba(40, 59, 105, 0.45), 0 2px 4px -2px rgba(40, 59, 105, 0.2)',
            },
        },
    },

    plugins: [forms],
};
