import defaultTheme from 'tailwindcss/defaultTheme'

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./build/output/*.html",
  ],
  darkMode: 'selector',
  theme: {
    extend: {
        fontFamily: {
            mono: ['"Roboto Mono"', ...defaultTheme.fontFamily.mono],
        },
        colors: {
            violet: {
                '25': '#f8f5ff',
            }
        }
    },
  },
  plugins: [],
}

