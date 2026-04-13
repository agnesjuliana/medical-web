import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  base: '/modules/modul_8/app/dist/',
  server: {
    port: 5173,
    hmr: {
      host: 'localhost',
      port: 5173,
    },
    // Proxy PHP API calls to the main PHP server
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/api/, ''),
      },
    },
  },
  build: {
    outDir: 'dist',
    manifest: true,
    rollupOptions: {
      input: 'src/main.tsx',
    },
  },
})
