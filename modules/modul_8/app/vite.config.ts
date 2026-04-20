import path from 'path'
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig(({ command }) => ({
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
      '/modules': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      }
    }
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
