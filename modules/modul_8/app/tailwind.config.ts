import type { Config } from 'tailwindcss'

export default {
  content: [
    './src/**/*.{js,ts,jsx,tsx}',
    './index.html',
  ],
  theme: {
    extend: {},
  },
  plugins: [],
  darkMode: 'class',
} satisfies Config
