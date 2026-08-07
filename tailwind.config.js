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
                        gold: {
                            DEFAULT: 'rgb(212 168 83 / <alpha-value>)',
                            50: 'rgb(250 246 212 / <alpha-value>)',
                            200: 'rgb(242 224 136 / <alpha-value>)',
                            500: 'rgb(222 195 40 / <alpha-value>)',
                            soft: 'rgb(250 246 235 / <alpha-value>)',
                        },
                        sky: 'rgb(94 179 255 / <alpha-value>)',
                        coral: 'rgb(255 138 101 / <alpha-value>)',
                    },
                },
            },
            fontFamily: {
                sans: ['Inter', 'Open Sans', ...defaultTheme.fontFamily.sans],
                display: ['Montserrat', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                'hero-card': '0 1px 2px rgba(40, 59, 105, 0.04), 0 12px 32px -16px rgba(40, 59, 105, 0.18)',
                'hero-cta': '0 10px 28px -10px rgba(212, 168, 83, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.35)',
                'hero-panel': '0 0 0 1px rgba(255,255,255,0.75) inset, 0 8px 32px -12px rgba(40,59,105,0.16)',
                'hero-glow': '0 8px 32px -8px rgba(212, 168, 83, 0.35)',
            },
            backgroundImage: {
                'hero-gold': 'var(--gradient-gold)',
                'hero-gold-soft': 'var(--gradient-gold-soft)',
                'hero-gold-cta': 'var(--gradient-gold-cta)',
            },
            borderRadius: {
                '4xl': '2rem',
            },
            spacing: {
                18: '4.5rem',
                22: '5.5rem',
                30: '7.5rem',
            },
            keyframes: {
                'fade-up': {
                    '0%': { opacity: '0', transform: 'translateY(10px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                shimmer: {
                    '0%': { backgroundPosition: '-200% 0' },
                    '100%': { backgroundPosition: '200% 0' },
                },
            },
            animation: {
                'fade-up': 'fade-up 0.45s cubic-bezier(0.22, 1, 0.36, 1) both',
                shimmer: 'shimmer 2s linear infinite',
            },
        },
    },

    plugins: [forms],
};
