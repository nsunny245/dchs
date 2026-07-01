/**
 * Daniyal Group of Colleges — Design Tokens (JS/TS)
 * Usable directly as a theme object in React, React Native, or any JS backend
 * that needs to render styled output (e.g. server-generated HTML/PDF).
 */

export const brand = {
  name: 'Daniyal Group of Colleges',
  tagline: 'Where Success Is a Tradition',
};

export const color = {
  navy: {
    50: '#EAEDF2',
    100: '#D2D8E3',
    200: '#A6B0C4',
    400: '#4C5C7A',
    700: '#1A2E4F',
    900: '#12223C', // primary brand color
    950: '#0A1526',
  },
  gold: {
    50: '#FDF4E4',
    100: '#FBE7C4',
    300: '#F3CD8B',
    500: '#EBB45A', // primary accent color
    600: '#D89A34',
    700: '#B37B22',
  },
  white: '#FFFFFF',
  black: '#000000',
  semantic: {
    success: '#1E8A5F',
    warning: '#EBB45A',
    danger: '#C0392B',
    info: '#2C6FAD',
  },
  surface: {
    light: '#F7F8FA',
    dark: '#0A1526',
    border: '#E1E4EA',
  },
  text: {
    primary: '#12223C',
    secondary: '#4C5C7A',
    inverse: '#FFFFFF',
  },
};

export const typography = {
  fontFamily: {
    display: 'Montserrat',
    body: 'Inter',
    tagline: 'Oswald',
    arabic: 'Noto Naskh Arabic',
  },
  scale: {
    displayXl: { fontSize: 48, lineHeight: 56, fontWeight: '800' },
    displayLg: { fontSize: 36, lineHeight: 44, fontWeight: '800' },
    heading1: { fontSize: 28, lineHeight: 36, fontWeight: '700' },
    heading2: { fontSize: 22, lineHeight: 30, fontWeight: '700' },
    heading3: { fontSize: 18, lineHeight: 26, fontWeight: '600' },
    bodyLg: { fontSize: 16, lineHeight: 24, fontWeight: '400' },
    bodySm: { fontSize: 14, lineHeight: 20, fontWeight: '400' },
    label: { fontSize: 12, lineHeight: 16, fontWeight: '600', letterSpacing: 0.5, textTransform: 'uppercase' },
  },
};

export const spacing = {
  1: 4, 2: 8, 3: 12, 4: 16, 5: 24, 6: 32, 7: 48, 8: 64, 9: 96,
};

export const radius = {
  sm: 4, md: 8, lg: 16, full: 999,
};

export const shadow = {
  sm: '0 1px 2px rgba(18,34,60,0.06)',
  md: '0 4px 12px rgba(18,34,60,0.10)',
  lg: '0 12px 32px rgba(18,34,60,0.16)',
};

export const dataVizOrder = ['#12223C', '#EBB45A', '#4C5C7A', '#F3CD8B', '#1E8A5F', '#2C6FAD'];

// Convenience default export — import as `theme` in RN/React contexts
const theme = { brand, color, typography, spacing, radius, shadow, dataVizOrder };
export default theme;
