import defaultTheme from 'tailwindcss/defaultTheme'

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./build/output/*.html",
  ],
  theme: {
    extend: {
        fontFamily: {
            mono: ['"Roboto Mono"', ...defaultTheme.fontFamily.mono],
        }
    },
  },
  plugins: [],
}

