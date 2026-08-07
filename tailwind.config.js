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
                    primary: 'rgb(40 59 105 / <alpha-value>)',
                    'primary-hover': 'rgb(30 45 80 / <alpha-value>)',
                    'primary-pressed': 'rgb(22 35 57 / <alpha-value>)',
                    'primary-soft': 'rgb(238 241 247 / <alpha-value>)',
                    secondary: 'rgb(255 255 255 / <alpha-value>)',
                    accent: {
                        teal: 'rgb(62 207 202 / <alpha-value>)',
                        gold: 'rgb(212 168 83 / <alpha-value>)',
                        sky: 'rgb(94 179 255 / <alpha-value>)',
                        coral: 'rgb(255 138 101 / <alpha-value>)',
                    },
                },
            },
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                'hero-card': '0 8px 30px -12px rgba(40, 59, 105, 0.16)',
                'hero-cta': '0 6px 18px -4px rgba(40, 59, 105, 0.42), 0 2px 4px -2px rgba(40, 59, 105, 0.18)',
                'hero-panel': '0 0 0 1px rgba(255,255,255,0.75) inset, 0 4px 24px -8px rgba(40,59,105,0.12)',
            },
            borderRadius: {
                '4xl': '2rem',
            },
        },
    },

    plugins: [forms],
};
