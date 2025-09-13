import { defineConfig } from 'vite'
import { svelte } from '@sveltejs/vite-plugin-svelte'
import { resolve } from 'path'

// Frontend Svelte Configuration
export default defineConfig({
  plugins: [svelte()],
  
  build: {
    outDir: 'dist/frontend',
    lib: {
      entry: resolve(__dirname, 'src/frontend/main.js'),
      name: 'ZeFunnel',
      fileName: 'ze-funnel',
      formats: ['iife']
    },
    rollupOptions: {
      output: {
        entryFileNames: 'ze-funnel.js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name === 'style.css') return 'ze-funnel.css'
          return assetInfo.name
        }
      }
    },
    sourcemap: true,
    minify: 'terser'
  },
  
  resolve: {
    alias: {
      '@': resolve(__dirname, 'src'),
      '@frontend': resolve(__dirname, 'src/frontend'),
      '@shared': resolve(__dirname, 'src/shared')
    }
  },
  
  
  define: {
    // WordPress globals
    'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV || 'production')
  }
})