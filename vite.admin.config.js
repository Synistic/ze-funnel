import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

// Admin Vue Configuration  
export default defineConfig({
  plugins: [vue()],
  
  build: {
    outDir: 'dist/admin',
    lib: {
      entry: resolve(__dirname, 'src/admin/main.js'),
      name: 'ZeFunnelAdmin',
      fileName: 'ze-funnel-admin',
      formats: ['iife']
    },
    rollupOptions: {
      output: {
        entryFileNames: 'ze-funnel-admin.js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name === 'style.css') return 'ze-funnel-admin.css'
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
      '@admin': resolve(__dirname, 'src/admin'),
      '@shared': resolve(__dirname, 'src/shared')
    }
  },
  
  css: {
    postcss: {
      plugins: [
        require('tailwindcss'),
        require('autoprefixer')
      ]
    }
  },
  
  define: {
    // WordPress admin globals
    'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV || 'production'),
    '__VUE_OPTIONS_API__': true,
    '__VUE_PROD_DEVTOOLS__': false
  }
})