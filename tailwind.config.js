import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import daisyui from 'daisyui';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: '#16a34a',
            },
        },
    },

    plugins: [forms, daisyui],

    daisyui: {
        themes: [
            {
                banksampah: {
                    "primary": "#16a34a",
                    "secondary": "#059669",
                    "accent": "#0d9488",
                    "neutral": "#1f2937",
                    "base-100": "#ffffff",
                    "info": "#0284c7",
                    "success": "#16a34a",
                    "warning": "#eab308",
                    "error": "#dc2626",
                },
            },
            "light",
        ],
    },
};
