import path from 'path'
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig(({ command }) => ({
  test: {
    environment: 'jsdom',
    globals: true,
    setupFiles: ['./src/test-setup.ts'],
  },
  plugins: [
    tailwindcss(),
    react(),
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  server: {
    host: '0.0.0.0',
    port: 5173,
    cors: true,
    proxy: {
      '/modules/modul_8/Backend': 'http://localhost:8000',
      '/modules/modul_8/api.php': 'http://localhost:8000',
    },
  },
  base: command === 'build' ? '/modules/modul_8/app/dist/' : '/',
  build: {
    outDir: 'dist',
    manifest: true,
    rollupOptions: {
      input: 'src/main.tsx',
    },
  },
}))
