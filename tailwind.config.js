/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./src/**/*.{js,ts,jsx,tsx,svelte,vue}",
    "./php/**/*.php"
  ],
  theme: {
    extend: {
      colors: {
        'ze-primary': {
          50: '#eff6ff',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8'
        }
      },
      fontFamily: {
        sans: ['-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif']
      }
    },
  },
  plugins: [
    require('@tailwindcss/forms')
  ],
  prefix: 'ze-',
  corePlugins: {
    preflight: false // Don't interfere with WordPress styles
  }
}