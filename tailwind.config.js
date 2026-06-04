/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#f0f9ff',
                    100: '#e0f2fe',
                    500: '#102a43', // Dark Navy
                    600: '#0a1c35', // Deep Navy from logo
                    800: '#0a1c35', // Match logo navy
                    900: '#050c1a',
                },
                gold: {
                    400: '#e1b96a',
                    500: '#c5a059', // Muted Gold from logo
                    600: '#a3844a',
                }
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', 'sans-serif'],
                serif: ['Playfair Display', 'serif'],
            }
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
};
