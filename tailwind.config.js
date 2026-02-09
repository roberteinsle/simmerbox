import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
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
                olive: {
                    50: '#f6f9f0',
                    100: '#eaf1db',
                    200: '#d5e3ba',
                    300: '#b5ce8d',
                    400: '#94b665',
                    500: '#759943',
                    600: '#61902c',
                    700: '#4a6e23',
                    800: '#3d5a21',
                    900: '#354c1f',
                    950: '#1b290d',
                },
            },
        },
    },

    plugins: [forms],
};
