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
                navy: {
                    50: '#EAEDF2',
                    100: '#D2D8E3',
                    200: '#A6B0C4',
                    400: '#4C5C7A',
                    700: '#1A2E4F',
                    900: '#12223C',
                    950: '#0A1526',
                },
                gold: {
                    50: '#FDF4E4',
                    100: '#FBE7C4',
                    300: '#F3CD8B',
                    500: '#EBB45A',
                    600: '#D89A34',
                    700: '#B37B22',
                },
                success: '#1E8A5F',
                warning: '#EBB45A',
                danger: '#C0392B',
                info: '#2C6FAD',
                brand: {
                    surface: '#F7F8FA',
                    border: '#E1E4EA',
                }
            },
            fontFamily: {
                display: ['Montserrat', 'Poppins', 'sans-serif'],
                body: ['Inter', 'sans-serif'],
                tagline: ['Oswald', 'sans-serif'],
                arabic: ['Noto Naskh Arabic', 'serif'],
            },
            borderRadius: {
                sm: '4px',
                md: '8px',
                lg: '16px',
                full: '999px',
            },
            boxShadow: {
                sm: '0 1px 2px rgba(18,34,60,0.06)',
                md: '0 4px 12px rgba(18,34,60,0.10)',
                lg: '0 12px 32px rgba(18,34,60,0.16)',
            }
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
};
